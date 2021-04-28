<?php

namespace OxidProfessionalServices\EasyCredit\Core\Domain;

/**
 * Class oxpsEasyCreditOxOrder
 */
class EasyCreditOrder extends EasyCreditOrder_parent {

    /** @var string */
    const EASYCREDIT_BESTELLUNG_BESTAETIGT = "BestellungBestaetigenServiceActivity.Infos.ERFOLGREICH";

    /** @var oxpsEasyCreditDic */
    private $dic = false;

    /**
     * Overrides standard oxid finalizeOrder method to handle easyCredit payment
     *
     * @param oxBasket $oBasket
     * @param oxUser $oUser
     * @param bool $blRecalculatingOrder
     *
     * @return bool
     */
    public function finalizeOrder(oxBasket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        if(!$this->isEasyCreditInstalmentPayment($oBasket->getPaymentId())) {
            return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
        }

        $result = false;
        try {
            $result = $this->finalizeEasyCreditOrder($oBasket, $oUser, $blRecalculatingOrder);
        }
        catch(oxpsEasyCreditInitializationFailedException $iex) {
            $this->handleUserException($iex->getMessage());
            oxRegistry::getUtils()->redirect($this->getConfig()->getShopCurrentURL() . '&cl=payment', true, 302);
        }
        catch(Exception $ex) {
            $this->handleException($ex);
        }

        return $result;
    }

    /**
     * Set additional attributes to order if payment is easycredit instalment
     *
     * @param oxBasket $oBasket
     */
    protected function _loadFromBasket(oxBasket $oBasket)
    {
        parent::_loadFromBasket($oBasket);

        if( $this->isEasyCreditInstalmentPayment($oBasket->getPaymentId()) ) {
            $storage = $this->getInstalmentStorage();
            if ($storage) {
                $this->oxorder__ecredinterestsvalue = new oxField($oBasket->getInterestsAmount());
                $this->oxorder__ecredpaymentstatus  = new oxField("not captured");

                $this->oxorder__ecredtechnicalid  = new oxField($storage->getTbVorgangskennung());
                $this->oxorder__ecredfunctionalid = new oxField($storage->getFachlicheVorgangskennung());
            }
        }
    }

    /**
     * Finalize order in OXID. Confirm payment in easyCredit.
     *
     * @param oxBasket $oBasket
     * @param oxUser $oUser
     * @param bool $blRecalculatingOrder
     *
     * @return mixed
     */
    protected function finalizeEasyCreditOrder(oxBasket $oBasket, $oUser, $blRecalculatingOrder = false)
    {

        $this->checkInitialization($oUser, $oBasket);

        $this->checkStorageDataIsComplete();

        $result = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        // the order can be completely ok or at most the ordermail cannot be sent
        if (
            in_array($result, [self::ORDER_STATE_OK, self::ORDER_STATE_MAILINGERROR])
        ) {
            $result = $this->confirmOrder($result);
        }

        return $result;
    }

    /**
     * Confirm payment in easyCredit
     *
     * @param $result
     *
     * @return mixed
     */
    protected function confirmOrder($result) {

        try {
            $response = $this->getConfirmResponse();

            $isConfirmed = $this->isConfirmed($response);

            $this->oxorder__ecredconfirmresponse = new oxField(base64_encode(serialize($response)), oxField::T_RAW);
            $this->oxorder__ecredpaymentstatus = new oxField($this->getPaymentStatus($isConfirmed), oxField::T_RAW);

            if(!$isConfirmed) {
                $this->oxorder__oxtransstatus = new oxField('ERROR', oxField::T_RAW);
                $this->handleUserException("OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED");
            }
            $this->save();
        }
        catch(Exception $ex) {
            $this->handleException($ex);
        }

        $this->updateAquisitionBorder();

        return $result;
    }

    /**
     * Update aquisition border
     */
    protected function updateAquisitionBorder()
    {
        /** @var $aquisitionBorder EasyCreditAquisitionBorder */
        $aquisitionBorder = oxNew("EasyCreditAquisitionBorder");
        $aquisitionBorder->updateAquisitionBorder();
    }

    /**
     * Checks response if payment is confirmed by easyCredit. Can be deactivated by config option!
     *
     * @param $response
     *
     * @return bool
     */
    protected function isConfirmed($response)
    {
        $validateResponse = $this->getConfig()->getConfigParam('oxpsECCheckoutValidConfirm');
        if(!$validateResponse) {
            return true;
        }

        if( empty($response) || !is_object($response) ) {
            return false;
        }

        $wsMessages = $response->wsMessages;
        if( empty($wsMessages) || !is_object($wsMessages) ) {
            return false;
        }

        $messages = $wsMessages->messages;
        if( empty($messages) || !is_array($messages) ) {
            return false;
        }

        $firstMessage = $messages[0];
        if(!is_object($firstMessage)) {
            return false;
        }
        if($firstMessage->key != self::EASYCREDIT_BESTELLUNG_BESTAETIGT) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether instalment store is valid
     *
     * @throws oxpsEasyCreditInitializationFailedException
     * @throws oxSystemComponentException
     */
    protected function checkStorageDataIsComplete()
    {
        /** @var $storage oxpsEasyCreditStorage */
        $storage = $this->getInstalmentStorage();

        $tbVorgangskennung = $storage->getTbVorgangskennung();
        if(!$tbVorgangskennung) {
            throw new oxpsEasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_PROCESS_ID_MISSING");
        }

        $fachlicheVorgangskennung = $storage->getFachlicheVorgangskennung();
        if(!$fachlicheVorgangskennung) {
            throw new oxpsEasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_FUNC_PROCESS_ID_MISSING");
        }

        $allgemeineVorgangsdaten = $storage->getAllgemeineVorgangsdaten();
        if(!$allgemeineVorgangsdaten) {
            throw new oxpsEasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_FUNC_PROCESSDATA_MISSING");
        }

        $tilgungsplanTxt = $storage->getTilgungsplanTxt();
        if(!$tilgungsplanTxt) {
            throw new oxpsEasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_FUNC_REDEMPTIONPLAN_MISSING");
        }

        $ratenplanTxt = $storage->getRatenplanTxt();
        if(!$ratenplanTxt) {
            throw new oxpsEasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_FUNC_PAYMENTPLAN_MISSING");
        }
    }

    /**
     * Checks whether initialization is valid
     *
     * @param $oUser oxUser
     * @param $oBasket oxBasket
     *
     * @throws oxpsEasyCreditInitializationFailedException
     */
    protected function checkInitialization($oUser, $oBasket)
    {
        //init basket price was calculated without interests -> recalculate without these costs to have the same value
        $this->calculateBasket($oBasket, true);

        //check payment hash again
        $data = $this->getCurrentInitializationData($oUser, $oBasket);
        $paymentHash = oxpsEasyCreditInitializeRequestBuilder::generatePaymentHash($data);

        $isInitialized = $this->isInitialized($paymentHash, $oBasket);

        $this->calculateBasket($oBasket);
        if(!$isInitialized) {
            throw new oxpsEasyCreditInitializationFailedException();
        }
    }

    /**
     * Gets data for usage in initialization process
     *
     * @return array the data
     */
    protected function getCurrentInitializationData($oUser, $oBasket)
    {
        $requestBuilder = oxNew('EasyCreditInitializeRequestBuilder');

        $requestBuilder->setUser($oUser);
        $requestBuilder->setBasket($oBasket);
        $requestBuilder->setShippingAddress($this->getShippingAddress());

        $shopEdition = oxpsEasyCreditHelper::getShopSystem($this->getConfig()->getActiveShop());
        $requestBuilder->setShopEdition($shopEdition);

        $moduleVersion = oxpsEasyCreditHelper::getModuleVersion($this->getDic());
        $requestBuilder->setModuleVersion($moduleVersion);

        $requestBuilder->setBaseLanguage(oxRegistry::getLang()->getBaseLanguage());

        $data = $requestBuilder->getInitializationData();
        return $data;
    }

    /**
     * Checks, if initialization is already done in the past
     *
     * @param $newPaymentHash unique hash of paymen
     * @param $basket oxBasket
     *
     * @return bool
     */
    protected function isInitialized($newPaymentHash, $basket)
    {
        $storage = $this->getInstalmentStorage();
        if( empty($storage) ) {
            return false;
        }

        $basketPrice = $basket->getPrice()->getPrice();
        if( $storage->getAuthorizationHash() !== $newPaymentHash || $storage->getAuthorizedAmount() !== $basketPrice) {
            return false;
        }
        return true;
    }

    /**
     * Returns shipping address
     * @return oxAddress
     */
    protected function getShippingAddress()
    {
        /** @var $oOrder oxOrder */
        $oOrder = oxNew('oxorder');
        return $oOrder->getDelAddressInfo();
    }

    /**
     * Returns the dic container.
     *
     * @return oxpsEasyCreditDic
     * @throws oxSystemComponentException
     */
    protected function getDic()
    {
        if(!$this->dic) {
            $this->dic = oxpsEasyCreditDicFactory::getDic();
        }

        return $this->dic;
    }

    /**
     * @return oxpsEasyCreditApiConfig
     */
    protected function getApiConfig()
    {
        return $this->getDic()->getApiConfig();
    }

    /**
     * Handles exception
     * @param $ex Exception
     */
    protected function handleException($ex)
    {
        $this->handleUserException($ex->getMessage());
        $this->getDic()->getLogging()->log($ex->getMessage());
    }

    /**
     * Sets message for displaying on frontend
     *
     * @param $i18nMessage
     */
    protected function handleUserException($i18nMessage)
    {
        $oEx = oxNew('oxExceptionToDisplay');
        $oEx->setMessage($i18nMessage);
        oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
    }

    /**
     * Recalculates Basket
     *
     * @param $oBasket oxBasket
     */
    protected function calculateBasket($oBasket, $excludeInstalmentsCosts = false)
    {
        $oBasket->setExcludeInstalmentsCosts($excludeInstalmentsCosts);
        $oBasket->onUpdate();
        $oBasket->calculateBasket(true);
        $oBasket->setExcludeInstalmentsCosts(false);
    }

    /**
     * Returns easycredit processdata
     *
     * @return oxpsEasyCreditStorage
     * @throws oxSystemComponentException
     */
    protected function getInstalmentStorage()
    {
        return $this->getDic()->getSession()->getStorage();
    }

    /**
     * @param $paymentId string
     *
     * @return bool
     */
    protected function isEasyCreditInstalmentPayment($paymentId)
    {
        return $paymentId == $this->getApiConfig()->getEasyCreditInstalmentPaymentId();
    }

    /**
     * Returns easycredit Tilgungsplan
     * only available as far as storage exists; method will be used for order email
     *
     * @return oxpsEasyCreditStorage
     * @throws oxSystemComponentException
     */
    public function getTilgungsplanTxt()
    {
        $storage = $this->getInstalmentStorage();
        if( $storage ) {
            return $storage->getTilgungsplanTxt();
        }
        return null;
    }

    /**
     * Returns easycredit interests (user formatted)
     *
     * @return string
     */
    public function getFInterestsValue()
    {
        $interestsValue = $this->oxorder__ecredinterestsvalue->value;
        if( $interestsValue ) {
            return oxRegistry::getLang()->formatCurrency($interestsValue, $this->getOrderCurrency());
        }
        return null;
    }

    /**
     * Returns easycredit internal payment status
     *
     * @return string
     */
    protected function getPaymentStatus($isConfirmed)
    {
        $paymentStatus = "failed";
        if( $isConfirmed ) {
            $paymentStatus = "completed";
        }
        return $paymentStatus;
    }

    public function validateOrder($oBasket, $oUser)
    {
        return parent::validateOrder($oBasket, $oUser);
    }

    protected function getConfirmResponse()
    {
        $processId = $this->getInstalmentStorage()->getTbVorgangskennung();
        $this->getDic()->getSession()->clearStorage();

        $wsClient = oxpsEasyCreditWebServiceClientFactory::getWebServiceClient(
            oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_BESTAETIGEN
            , $this->getDic()
            , array($processId)
            , array()
            , true);
        return $wsClient->execute();
    }
}
