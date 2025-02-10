<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Domain;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\ExceptionToDisplay;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopProfessional\Core\DatabaseProvider;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditInitializationFailedException;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditInitializeRequestBuilder;

/**
 * Class oxpsEasyCreditOxOrder
 */
class EasyCreditOrder extends EasyCreditOrder_parent
{
    /** @var string */
    public const EASYCREDIT_BESTELLUNG_BESTAETIGT = "BestellungBestaetigenServiceActivity.Infos.ERFOLGREICH";

    /** @var EasyCreditDic */
    private $dic = false;

    /**
     * Overrides standard oxid finalizeOrder method to handle easyCredit payment
     *
     * @param Basket $oBasket
     * @param User $oUser
     * @param bool $blRecalculatingOrder
     *
     * @return bool
     */
    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        if (!$this->isEasyCreditInstalmentPayment($oBasket->getPaymentId())) {
            return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
        }

        $result = false;
        try {
            $result = $this->finalizeEasyCreditOrder($oBasket, $oUser, $blRecalculatingOrder);
        } catch (EasyCreditInitializationFailedException $iex) {
            $this->handleUserException($iex->getMessage());
            Registry::getUtils()->redirect($this->getConfig()->getShopCurrentURL() . '&cl=payment', true, 302);
        } catch (\Exception $ex) {
            $this->handleException($ex);
        }

        return $result;
    }

    /**
     * Set additional attributes to order if payment is easycredit instalment
     *
     * @param Basket $oBasket
     */
    protected function _loadFromBasket(Basket $oBasket)
    {
        parent::_loadFromBasket($oBasket);

        if ($this->isEasyCreditInstalmentPayment($oBasket->getPaymentId())) {
            $storage = $this->getInstalmentStorage();
            if ($storage) {
                $this->oxorder__ecredinterestsvalue = new Field($oBasket->getInterestsAmount());
                $this->oxorder__ecredpaymentstatus  = new Field("not captured");

                $this->oxorder__ecredtechnicalid  = new Field($storage->getTbVorgangskennung());
                $this->oxorder__ecredfunctionalid = new Field($storage->getFachlicheVorgangskennung());
                $this->oxorder__ecreddeliverystate = new Field(EasyCreditTradingApiAccess::OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN);
            }
        }
    }

    /**
     * Finalize order in OXID. Confirm payment in easyCredit.
     *
     * @param Basket $oBasket
     * @param User $oUser
     * @param bool $blRecalculatingOrder
     *
     * @return mixed
     */
    protected function finalizeEasyCreditOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
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
    protected function confirmOrder($result)
    {

        try {
            $response = $this->getConfirmResponse();

            $isConfirmed = $this->isConfirmed($response);

            $this->oxorder__ecredconfirmresponse = new Field(base64_encode(serialize($response)), Field::T_RAW);
            $this->oxorder__ecredpaymentstatus = new Field($this->getPaymentStatus($isConfirmed), Field::T_RAW);

            if (!$isConfirmed) {
                $this->oxorder__oxtransstatus = new Field('ERROR', Field::T_RAW);
                $this->handleUserException("OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED");
            }
            $this->save();
        } catch (\Exception $ex) {
            $this->handleException($ex);
        }

        return $result;
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
        if (!$validateResponse) {
            return true;
        }

        if (empty($response) || !is_object($response)) {
            return false;
        }

        $wsMessages = $response->wsMessages;
        if (empty($wsMessages) || !is_object($wsMessages)) {
            return false;
        }

        $messages = $wsMessages->messages;
        if (empty($messages) || !is_array($messages)) {
            return false;
        }

        $firstMessage = $messages[0];
        if (!is_object($firstMessage)) {
            return false;
        }
        if ($firstMessage->key != self::EASYCREDIT_BESTELLUNG_BESTAETIGT) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether instalment store is valid
     *
     * @throws EasyCreditInitializationFailedException
     * @throws SystemComponentException
     */
    protected function checkStorageDataIsComplete()
    {
        /** @var $storage EasyCreditStorage */
        $storage = $this->getInstalmentStorage();

        $tbVorgangskennung = $storage->getTbVorgangskennung();
        if (!$tbVorgangskennung) {
            throw new EasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_PROCESS_ID_MISSING");
        }

        $fachlicheVorgangskennung = $storage->getFachlicheVorgangskennung();
        if (!$fachlicheVorgangskennung) {
            throw new EasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_FUNC_PROCESS_ID_MISSING");
        }

        $allgemeineVorgangsdaten = $storage->getAllgemeineVorgangsdaten();
        if (!$allgemeineVorgangsdaten) {
            throw new EasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_FUNC_PROCESSDATA_MISSING");
        }

        $tilgungsplanTxt = $storage->getTilgungsplanTxt();
        if (!$tilgungsplanTxt) {
            throw new EasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_FUNC_REDEMPTIONPLAN_MISSING");
        }

        $ratenplanTxt = $storage->getRatenplanTxt();
        if (!$ratenplanTxt) {
            throw new EasyCreditInitializationFailedException("OXPS_EASY_CREDIT_ERROR_FUNC_PAYMENTPLAN_MISSING");
        }
    }

    /**
     * Checks whether initialization is valid
     *
     * @param $oUser User
     * @param $oBasket Basket
     *
     * @throws EasyCreditInitializationFailedException
     */
    protected function checkInitialization($oUser, $oBasket)
    {
        //init basket price was calculated without interests -> recalculate without these costs to have the same value
        $this->calculateBasket($oBasket, true);

        //check payment hash again
        $data = $this->getCurrentInitializationData($oUser, $oBasket);
        $paymentHash = EasyCreditInitializeRequestBuilder::generatePaymentHash($data);

        $isInitialized = $this->isInitialized($paymentHash, $oBasket);

        $this->calculateBasket($oBasket);
        if (!$isInitialized) {
            throw new EasyCreditInitializationFailedException();
        }
    }

    /**
     * Gets data for usage in initialization process
     *
     * @return array the data
     */
    protected function getCurrentInitializationData($oUser, $oBasket)
    {
        $requestBuilder = oxNew(EasyCreditInitializeRequestBuilder::class);

        $requestBuilder->setUser($oUser);
        $requestBuilder->setBasket($oBasket);
        $requestBuilder->setShippingAddress($this->getShippingAddress());

        $shopEdition = EasyCreditHelper::getShopSystem($this->getConfig()->getActiveShop());
        $requestBuilder->setShopEdition($shopEdition);

        $moduleVersion = EasyCreditHelper::getModuleVersion($this->getDic());
        $requestBuilder->setModuleVersion($moduleVersion);

        $requestBuilder->setBaseLanguage(Registry::getLang()->getBaseLanguage());

        $data = $requestBuilder->getInitializationData();
        return $data;
    }

    /**
     * Checks, if initialization is already done in the past
     *
     * @param $newPaymentHash string unique hash of paymen
     * @param $basket Basket
     *
     * @return bool
     */
    protected function isInitialized($newPaymentHash, $basket)
    {
        $storage = $this->getInstalmentStorage();
        if (empty($storage)) {
            return false;
        }

        $basketPrice = $basket->getPrice()->getPrice();
        if ($storage->getAuthorizationHash() !== $newPaymentHash || $storage->getAuthorizedAmount() !== $basketPrice) {
            return false;
        }
        return true;
    }

    /**
     * Returns shipping address
     * @return Address
     */
    protected function getShippingAddress()
    {
        /** @var $oOrder Order */
        $oOrder = oxNew(Order::class);
        return $oOrder->getDelAddressInfo();
    }

    /**
     * Returns the dic container.
     *
     * @return EasyCreditDic
     * @throws SystemComponentException
     */
    protected function getDic()
    {
        if (!$this->dic) {
            $this->dic = EasyCreditDicFactory::getDic();
        }

        return $this->dic;
    }

    /**
     * @return EasyCreditApiConfig
     */
    protected function getApiConfig()
    {
        return $this->getDic()->getApiConfig();
    }

    /**
     * Handles exception
     * @param $ex \Exception
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
        $oEx = oxNew(ExceptionToDisplay::class);
        $oEx->setMessage($i18nMessage);
        Registry::get("oxUtilsView")->addErrorToDisplay($oEx);
    }

    /**
     * Recalculates Basket
     *
     * @param $oBasket Basket
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
     * @return EasyCreditStorage|null
     * @throws SystemComponentException
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
     * @return EasyCreditStorage
     * @throws SystemComponentException
     */
    public function getTilgungsplanTxt()
    {
        $storage = $this->getInstalmentStorage();
        if ($storage) {
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
        if (!isset($this->oxorder__ecredinterestsvalue) || !is_object($this->oxorder__ecredinterestsvalue)) {
            return null;
        }

        $interestsValue = $this->oxorder__ecredinterestsvalue->value;
        if ($interestsValue) {
            return Registry::getLang()->formatCurrency($interestsValue, $this->getOrderCurrency());
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
        if ($isConfirmed) {
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

        $requestData = [
            'shopVorgangskennung' => $this->oxorder__oxordernr->value,
        ];

        $wsClient = EasyCreditWebServiceClientFactory::getWebServiceClient(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_BESTAETIGEN,
            $this->getDic(),
            [$processId],
            [],
            true
        );
        return $wsClient->execute($requestData);
    }

    /**
     * @param $functionalId
     *
     * @throws EasyCreditException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function loadByECFunctionalId($functionalId)
    {
        $viewName = $this->getViewName('oxorder');
        $sql = 'SELECT oxid FROM ' . $viewName . ' WHERE `ecredfunctionalid` = ?';
        $oxid = DatabaseProvider::getDb()->getOne($sql, [$functionalId]);
        if (!$this->load($oxid)) {
            throw new EasyCreditException("No order with functional ID: $functionalId ");
        }
    }
}
