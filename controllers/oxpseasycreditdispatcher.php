<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

/**
 * EasyCredit checkout dispatcher class
 *
 * Handles requests to easyCredit and process reponse from easyCredit
 */
class oxpsEasyCreditDispatcher extends oxUBase
{
    const INSTALMENT_DECISION_OK = "GRUEN";

    /** @var oxpsEasyCreditDic */
    private $dic = false;

    /** @var oxpsEasyCreditApiConfig */
    private $apiConfig = false;

    /**
     * Executes initialization process via "VorgangInitialisierenRequest" and redirects user to easyCredit on success
     * easyCredit paymentpage returns to oxid payment page to show errors or to order page on success
     *
     * @return string targetpage
     */
    public function initializeandredirect()
    {
        $this->calculateBasket($this->getApiConfig()->getEasyCreditInstalmentPaymentId(), $this->getBasket(), true);

        try {
            $currentInitData = $this->getCurrentInitializationData();
            $currentPaymentHash = oxpsEasyCreditInitializeRequestBuilder::generatePaymentHash($currentInitData);
            if(!$this->isInitialized($currentPaymentHash) ) {
                $this->initialize($currentPaymentHash, $currentInitData);
            }
            $this->redirectToEasyCredit();
        }
        catch(Exception $ex) {
            $this->handleException($ex);
        }
        return "payment";
    }

    /**
     * Will be called by easyCredit
     * Process response of easycredit about instalment process.
     *
     * @return string pages "order" or "payment" to display errors
     */
    public function getEasyCreditDetails()
    {
        try {
            $this->processEasyCreditDetails();
            return "order";
        }
        catch(Exception $ex) {
            $this->getDicSession()->clearStorage();
            $this->getBasket()->setPayment(null);
            $this->handleUserException($ex->getMessage());
        }
        return "payment";
    }

    /**
     * Validates easyCredit response
     *
     * @return string
     */
    protected function processEasyCreditDetails() {

        $this->checkInitialization();

        $this->checkAuthorization();

        $this->loadEasyCreditFinancialInformation();

        $this->setEasyCreditInstalmentAsCurrentPayment();

        return "order";
    }

    /**
     * Redirect user to easyCredit paymentpage
     */
    protected function redirectToEasyCredit()
    {
        $sUrl = $this->getRedirectUrl();
        oxRegistry::getUtils()->redirect($sUrl, false);
    }

    /**
     * Checks, if initialization is already done in the past
     *
     * @param $newPaymentHash unique hash of payment
     *
     * @return bool
     */
    protected function isInitialized($newPaymentHash)
    {
        $storage = $this->getInstalmentStorage();
        if( empty($storage) ) {
            return false;
        }

        if(!$storage->getTbVorgangskennung() ) {
            return false;
        }

        $basketPrice = $this->getBasketPrice();
        if( $storage->getAuthorizationHash() !== $newPaymentHash || $storage->getAuthorizedAmount() !== $basketPrice) {
            return false;
        }
        return true;
    }

    /**
     * Initialize new process
     * @param $authorizationHash new hash
     * @param $data basket/user data
     * @throws Exception
     */
    protected function initialize($authorizationHash, $data)
    {
        $this->getDicSession()->clearStorage();

        $response = $this->call(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_VORGANG
            , array()
            , array()
            , $data);

        $storage = oxNew("oxpsEasyCreditStorage",
            $response->tbVorgangskennung,
            $response->fachlicheVorgangskennung,
            $authorizationHash,
            $this->getBasketPrice()
        );
        $this->getDicSession()->setStorage($storage);
    }

    /**
     * Gets data for usage in initialization process
     *
     * @return array the data
     */
    protected function getCurrentInitializationData()
    {
        $requestBuilder = $this->getInitializationRequestBuilder();

        $requestBuilder->setUser($this->getUser());
        $requestBuilder->setBasket($this->getBasket());
        $requestBuilder->setShippingAddress($this->getShippingAddress());
        $requestBuilder->setShopEdition($this->getShopSystem());
        $requestBuilder->setModuleVersion($this->getModuleVersion());
        $requestBuilder->setBaseLanguage(oxRegistry::getLang()->getBaseLanguage());

        $data = $requestBuilder->getInitializationData();
        return $data;
    }

    /**
     * Returns shop full edition
     *
     * @return string
     */
    public function getShopSystem()
    {
        return oxpsEasyCreditHelper::getShopSystem($this->getConfig()->getActiveShop());
    }

    /**
     * Save payment and recalculate basket
     *
     * @param $paymentId string
     * @param $basket oxBasket
     * @param $excludeCosts Should calculation exclude instalments?
     */
    protected function calculateBasket($paymentId, $basket, $excludeCosts = false)
    {
        $basket->setExcludeInstalmentsCosts($excludeCosts);
        $basket->setPayment($paymentId);
        $basket->onUpdate();
        $basket->calculateBasket(true);
        $basket->setExcludeInstalmentsCosts(false);
    }

    /**
     * Calls webservice to get decision for process
     *
     * @return string
     * @throws Exception
     */
    protected function getInstalmentDecision()
    {
        $response = $this->call(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_DECISION
            , array($this->getTbVorgangskennung())
            , array());

        if (!isset($response->entscheidung->entscheidungsergebnis)) {
            return null;
        }

        return $response->entscheidung->entscheidungsergebnis;
    }

    /**
     * Returns easyCredit module version
     *
     * @return string
     */
    protected function getModuleVersion()
    {
        return oxpsEasyCreditHelper::getModuleVersion($this->getDic());
    }

    /**
     * Returns basket
     * @return oxBasket
     */
    protected function getBasket()
    {
        return oxRegistry::getSession()->getBasket();
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
     * Returns url to easyCredit payment page
     * @return string
     */
    public function getRedirectUrl() {

        $storage = $this->getInstalmentStorage();
        $url = sprintf($this->getApiConfig()->getRedirectUrl(), $storage->getTbVorgangskennung());
        return $url;
    }

    /**
     * Returns easycredit processdata
     *
     * @return null|oxpsEasyCreditStorage
     */
    protected function getInstalmentStorage()
    {
        return $this->getDicSession()->getStorage();
    }

    /**
     * @return float
     */
    protected function getBasketPrice()
    {
        return $this->getBasket()->getPrice()->getPrice();
    }

    protected function checkInitialization()
    {

        $this->calculateBasket($this->getBasket()->getPaymentId(), $this->getBasket(), true);

        //check payment hash again
        $data = $this->getCurrentInitializationData();
        $paymentHash = oxpsEasyCreditInitializeRequestBuilder::generatePaymentHash($data);
        if(!$this->isInitialized($paymentHash)) {
            throw new oxpsEasyCreditException("OXPS_EASY_CREDIT_ERROR_INITIALIZATION_FAILED");
        }
    }

    /**
     * Checks if ratenkauf is approved by easyCredit
     *
     * @throws oxpsEasyCreditException will be thrown in failed state
     */
    protected function checkAuthorization()
    {
        if ($this->getInstalmentDecision() !== self::INSTALMENT_DECISION_OK) {
            throw new oxpsEasyCreditException("OXPS_EASY_CREDIT_ERROR_NOT_APPROVED");
        }
    }

    /**
     * Return current vorgangskennung
     *
     * @return string
     * @throws oxpsEasyCreditException if there is no vorgangskennung
     */
    protected function getTbVorgangskennung()
    {
        $storage = $this->getInstalmentStorage();
        if( $storage ) {
            return $storage->getTbVorgangskennung();
        }
        throw new oxpsEasyCreditException("OXPS_EASY_CREDIT_ERROR_MISSING_VORGANGSKENNUNG");
    }

    /**
     * Set easyCredit as current payment
     */
    protected function setEasyCreditInstalmentAsCurrentPayment()
    {
        $paymentId = $this->getApiConfig()->getEasyCreditInstalmentPaymentId();
        $this->getSession()->setVariable('paymentid', $paymentId);
        $this->getBasket()->setPayment($paymentId);
    }

    /**
     * Loads financial information about easycredit
     *
     * @throws Exception
     */
    protected function loadEasyCreditFinancialInformation()
    {
        $storage = $this->getInstalmentStorage();
        if( $storage == null ) {
            throw new oxpsEasyCreditException("OXPS_EASY_CREDIT_ERROR_EXPIRED");
        }

        $response = $this->call(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_FINANCIAL_INFORMATION, array($storage->getTbVorgangskennung()));
        $allgemeineVorgangsdaten = $response->allgemeineVorgangsdaten;
        $tilgungsplanText = $response->tilgungsplanText;

        $response = $this->call(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_FINANZIERUNG, array($storage->getTbVorgangskennung()));
        $paymentPlan = $response->ratenplan;
        $paymentPlanTxt = $this->getFormattedPaymentPlan($paymentPlan->zahlungsplan);

        $storage->setAllgemeineVorgangsdaten($allgemeineVorgangsdaten);
        $storage->setTilgungsplanTxt($tilgungsplanText);
        $storage->setRatenplanTxt($paymentPlanTxt);
        $storage->setInterestAmount($this->getInterestAmount($paymentPlan));

        $this->getDicSession()->setStorage($storage);
    }

    /**
     * Returns payment plan for user display
     *
     * @param $paymentPlan stdClass
     *
     * @return string
     */
    protected function getFormattedPaymentPlan($paymentPlan)
    {
        if (is_object($paymentPlan)) {

            $rateTotalCount = (int) $paymentPlan->anzahlRaten;
            $ratePerMonth   = (float) $paymentPlan->betragRate;
            $lastRate       = (float) $paymentPlan->betragLetzteRate;

            $paymentPlanPattern = oxRegistry::getLang()->translateString("OXPS_EASY_CREDIT_FORMATTED_PAYMENT_PLAN");
            return sprintf($paymentPlanPattern, $rateTotalCount, $ratePerMonth, $rateTotalCount - 1, $ratePerMonth, $lastRate);
        }
        return null;
    }

    /**
     * Returns interest amount
     *
     * @param $paymentPlan stdClass
     * @return string
     */
    protected function getInterestAmount($paymentPlan) {

        $interestAmount = (float)$paymentPlan->zinsen->anfallendeZinsen;
        if( empty($interestAmount) || $interestAmount < 0.0 ) {
            $interestAmount = 0.0;
        }
        return $interestAmount;
    }

    /**
     * Returns request builder for initialization new order process
     *
     * @return oxpsEasyCreditInitializeRequestBuilderInterface
     */
    protected function getInitializationRequestBuilder()
    {
        return oxNew('oxpsEasyCreditInitializeRequestBuilder');
    }

    /**
     * Calls webservice endpoint
     *
     * @var string $endpoint name of service
     * @var array $additionalArguments args which can be used in url
     * @var array $queryArguments query args
     * @var array $data postdata
     * @return string response of webservice
     * @throws Exception if something happened
     */
    protected function call($endpoint, $additionalArguments = array(), $queryArguments = array(), $data = null)
    {
        $webServiceClient = oxpsEasyCreditWebServiceClientFactory::getWebServiceClient($endpoint
            , $this->getDic()
            , $additionalArguments
            , $queryArguments
            , true);

        return $webServiceClient->execute($data);
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
     * Returns api config
     *
     * @return oxpsEasyCreditApiConfig
     */
    protected function getApiConfig()
    {
        if(!$this->apiConfig ) {
            $this->apiConfig = $this->getDic()->getApiConfig();
        }
        return $this->apiConfig;
    }

    /**
     * Returns dic session
     *
     * @return oxpsEasyCreditDicSession
     */
    protected function getDicSession()
    {
        return $this->getDic()->getSession();
    }
}