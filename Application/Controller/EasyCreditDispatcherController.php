<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Application\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\ExceptionToDisplay;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicSession;
use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditInitializeRequestBuilder;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditInitializeRequestBuilderInterface;

/**
 * EasyCredit checkout dispatcher class
 *
 * Handles requests to easyCredit and process reponse from easyCredit
 */
class EasyCreditDispatcherController extends FrontendController
{
    public const INSTALMENT_DECISION_OK = "GRUEN";

    /** @var EasyCreditDic */
    private $dic = false;

    /** @var EasyCreditApiConfig */
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
            $currentPaymentHash = EasyCreditInitializeRequestBuilder::generatePaymentHash($currentInitData);
            if (!$this->isInitialized($currentPaymentHash)) {
                $this->initialize($currentPaymentHash, $currentInitData);
            }
            $this->redirectToEasyCredit();
        } catch (\Exception $ex) {
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
        } catch (\Exception $ex) {
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
    protected function processEasyCreditDetails()
    {

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
        Registry::getUtils()->redirect($sUrl, false);
    }

    /**
     * Checks, if initialization is already done in the past
     *
     * @param $newPaymentHash string unique hash of payment
     *
     * @return bool
     */
    protected function isInitialized($newPaymentHash)
    {
        $storage = $this->getInstalmentStorage();
        if (empty($storage)) {
            return false;
        }

        if (!$storage->getTbVorgangskennung()) {
            return false;
        }

        $basketPrice = $this->getBasketPrice();
        if ($storage->getAuthorizationHash() !== $newPaymentHash || $storage->getAuthorizedAmount() !== $basketPrice) {
            return false;
        }
        return true;
    }

    /**
     * Initialize new process
     * @param $authorizationHash string hash
     * @param $data array basket/user data
     * @throws \Exception
     */
    protected function initialize($authorizationHash, $data)
    {
        $this->getDicSession()->clearStorage();

        $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_VORGANG, [], [], $data);

        $storage = oxNew(
            EasyCreditStorage::class,
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
        $requestBuilder->setBaseLanguage(Registry::getLang()->getBaseLanguage());

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
        return EasyCreditHelper::getShopSystem($this->getConfig()->getActiveShop());
    }

    /**
     * Save payment and recalculate basket
     *
     * @param $paymentId string
     * @param $basket Basket
     * @param $excludeCosts mixed Should calculation exclude instalments?
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
     * @throws \Exception
     */
    protected function getInstalmentDecision()
    {
        $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_DECISION, [$this->getTbVorgangskennung()], []);

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
        return EasyCreditHelper::getModuleVersion($this->getDic());
    }

    /**
     * Returns basket
     * @return Basket
     */
    protected function getBasket()
    {
        return Registry::getSession()->getBasket();
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
     * Returns url to easyCredit payment page
     * @return string
     */
    public function getRedirectUrl()
    {

        $storage = $this->getInstalmentStorage();
        $url = sprintf($this->getApiConfig()->getRedirectUrl(), $storage->getTbVorgangskennung());
        return $url;
    }

    /**
     * Returns easycredit processdata
     *
     * @return null|EasyCreditStorage
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

    /**
     * @throws EasyCreditException
     */
    protected function checkInitialization()
    {

        $this->calculateBasket($this->getBasket()->getPaymentId(), $this->getBasket(), true);

        //check payment hash again
        $data = $this->getCurrentInitializationData();
        $paymentHash = EasyCreditInitializeRequestBuilder::generatePaymentHash($data);
        if (!$this->isInitialized($paymentHash)) {
            throw new EasyCreditException("OXPS_EASY_CREDIT_ERROR_INITIALIZATION_FAILED");
        }
    }

    /**
     * Checks if ratenkauf is approved by easyCredit
     *
     * @throws EasyCreditException will be thrown in failed state
     */
    protected function checkAuthorization()
    {
        if ($this->getInstalmentDecision() !== self::INSTALMENT_DECISION_OK) {
            throw new EasyCreditException("OXPS_EASY_CREDIT_ERROR_NOT_APPROVED");
        }
    }

    /**
     * Return current vorgangskennung
     *
     * @return string
     * @throws EasyCreditException if there is no vorgangskennung
     */
    protected function getTbVorgangskennung()
    {
        $storage = $this->getInstalmentStorage();
        if ($storage) {
            return $storage->getTbVorgangskennung();
        }
        throw new EasyCreditException("OXPS_EASY_CREDIT_ERROR_MISSING_VORGANGSKENNUNG");
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
     * @throws \Exception
     */
    protected function loadEasyCreditFinancialInformation()
    {
        $storage = $this->getInstalmentStorage();
        if ($storage == null) {
            throw new EasyCreditException("OXPS_EASY_CREDIT_ERROR_EXPIRED");
        }

        $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_FINANCIAL_INFORMATION, [$storage->getTbVorgangskennung()]);
        $allgemeineVorgangsdaten = $response->allgemeineVorgangsdaten;
        $tilgungsplanText = $response->tilgungsplanText;

        $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_FINANZIERUNG, [$storage->getTbVorgangskennung()]);
        $paymentPlan = $response->ratenplan;
        $paymentPlanTxt = $this->getFormattedPaymentPlan($paymentPlan->zahlungsplan);

        $tilgungsplanText = $tilgungsplanText ? $tilgungsplanText : $response->tilgungsplanText;

        $storage->setAllgemeineVorgangsdaten($allgemeineVorgangsdaten);
        $storage->setTilgungsplanTxt($tilgungsplanText);
        $storage->setRatenplanTxt($paymentPlanTxt);
        $storage->setInterestAmount($this->getInterestAmount($paymentPlan));

        $this->getDicSession()->setStorage($storage);
    }

    /**
     * Returns payment plan for user display
     *
     * @param $paymentPlan \stdClass
     *
     * @return string
     */
    protected function getFormattedPaymentPlan($paymentPlan)
    {
        if (is_object($paymentPlan)) {

            $rateTotalCount = (int) $paymentPlan->anzahlRaten;
            $ratePerMonth   = (float) $paymentPlan->betragRate;
            $lastRate       = (float) $paymentPlan->betragLetzteRate;

            $paymentPlanPattern = Registry::getLang()->translateString("OXPS_EASY_CREDIT_FORMATTED_PAYMENT_PLAN");
            return sprintf($paymentPlanPattern, $rateTotalCount, $ratePerMonth, $rateTotalCount - 1, $ratePerMonth, $lastRate);
        }
        return null;
    }

    /**
     * Returns interest amount
     *
     * @param $paymentPlan \stdClass
     * @return string
     */
    protected function getInterestAmount($paymentPlan)
    {

        $interestAmount = (float) $paymentPlan->zinsen->anfallendeZinsen;
        if (empty($interestAmount) || $interestAmount < 0.0) {
            $interestAmount = 0.0;
        }
        return $interestAmount;
    }

    /**
     * Returns request builder for initialization new order process
     *
     * @return EasyCreditInitializeRequestBuilderInterface
     */
    protected function getInitializationRequestBuilder()
    {
        return oxNew(EasyCreditInitializeRequestBuilder::class);
    }

    /**
     * Calls webservice endpoint
     *
     * @var string $endpoint name of service
     * @var array $additionalArguments args which can be used in url
     * @var array $queryArguments query args
     * @var array $data postdata
     * @return string response of webservice
     * @throws \Exception if something happened
     */
    protected function call($endpoint, $additionalArguments = [], $queryArguments = [], $data = null)
    {
        $webServiceClient = EasyCreditWebServiceClientFactory::getWebServiceClient($endpoint, $this->getDic(), $additionalArguments, $queryArguments, true);

        return $webServiceClient->execute($data);
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
     * Returns api config
     *
     * @return EasyCreditApiConfig
     */
    protected function getApiConfig()
    {
        if (!$this->apiConfig) {
            $this->apiConfig = $this->getDic()->getApiConfig();
        }
        return $this->apiConfig;
    }

    /**
     * Returns dic session
     *
     * @return EasyCreditDicSession
     */
    protected function getDicSession()
    {
        return $this->getDic()->getSession();
    }
}
