<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Application\Controller;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Exception\ExceptionToDisplay;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Class oxpsEasyCreditPayment.
 * Extends Payment.
 *
 * @see Payment
 */
class EasyCreditPaymentController extends EasyCreditPaymentController_parent
{
    /** @var EasyCreditDic */
    private $dic;

    /** @var \\stdClass */
    private $exampleCalculation;

    /** @var array */
    private $errorMessages;

    /** @var object */
    protected $_oDelAddress;

    /** @var null|bool|\\stdClass */
    private $easyCreditPossible;

    /** @var string */
    private $agreementTxt = false;

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
     * Returns active basket
     *
     * @return Basket
     */
    protected function getBasket()
    {
        return Registry::getSession()->getBasket();
    }

    /**
     * Checks if ratenkauf is a valid payment in this checkout process. If not, false is returned.
     *
     * @return bool|\\stdClass
     * @throws SystemComponentException
     */
    public function isEasyCreditPossible()
    {
        if ($this->easyCreditPossible === null) {
            $this->checkEasyCreditPossible();
        }

        return $this->easyCreditPossible;
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditPossible()
    {
        $this->easyCreditPossible = true;

        $this->checkEasyCreditForeignAddress();

        $this->checkEasyCreditAddressMismatch();

        $this->checkEasyCreditPackstation();

        $this->checkEasyCreditAgreementTxt();

        $this->checkEasyCreditExampleCalulation();
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditForeignAddress()
    {
        if ($this->isForeignAddress()) {
            $this->errorMessages[]    = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_NO_GERMAN_ADDRESS');
            $this->easyCreditPossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditAddressMismatch()
    {
        if ($this->isAddressMismatch()) {
            $this->errorMessages[]    = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_DEL_ADDRESS');
            $this->easyCreditPossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditPackstation()
    {
        if ($this->isPackstation()) {
            $this->errorMessages[]    = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_PACKSTATION');
            $this->easyCreditPossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditAgreementTxt()
    {
        $agreements = $this->getAgreementTxt();
        if (empty($agreements)) {
            $this->errorMessages[]    = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_NO_AGREEMENTS');
            $this->easyCreditPossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditExampleCalulation()
    {
        $response = $this->getExampleCalulation();
        if (is_string($response)) {
            $this->easyCreditPossible = false;
        } else {
            $this->easyCreditPossible = $this->easyCreditPossible && true;
        }
    }

    /**
     * Returns the example calculation response or false if there was an error.
     *
     * @return bool|\stdClass
     * @throws SystemComponentException
     */
    protected function getExampleCalulation()
    {
        if (!$this->exampleCalculation) {
            $response = $this->getExampleCalculationResponse();
            if ($response) {
                $this->exampleCalculation = $response;
            }
        }

        return $this->exampleCalculation;
    }

    /**
     * Tries to get a valid example calculation response.
     *
     * @return bool|\stdClass
     * @throws SystemComponentException
     */
    protected function getExampleCalculationResponse()
    {
        $price = $this->getPrice();

        $payment = oxNew(Payment::class);
        $payment->load('easycreditinstallment');

        if (
            !$price ||
            (int) $price->getBruttoPrice() < (int) $payment->getFieldData('oxfromamount') ||
            (int) $price->getBruttoPrice() > (int) $payment->getFieldData('oxtoamount')
        ) {
            return false;
        }

        try {
            return $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN, [], [EasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_FINANZIERUNGSBETRAG => $price->getBruttoPrice()]);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Returns the price relevant for the example calculation.
     *
     * @return Price
     * @throws SystemComponentException
     */
    protected function getPrice()
    {
        return $this->getExampleCalculationPrice($this->getViewParameter("articleId"));
    }

    /**
     * Returns the price relevant for the example calculation.
     *
     * @param string $articleId
     *
     * @return Price|void
     * @throws SystemComponentException
     */
    public function getExampleCalculationPrice($articleId)
    {
        if ($articleId) {
            /** @var Article $article */
            $article = oxNew(Article::class);
            if ($article->load($articleId)) {
                return $article->getPrice();
            }
        } else {
            $basket = $this->getBasket();
            return $basket->getPrice();
        }
    }

    /**
     * Returns the error messages.
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getErrorMessages()
    {
        return array_unique($this->errorMessages);
    }

    /**
     * Template variable getter. Returns delivery address
     *
     * @return object
     * @throws SystemComponentException
     */
    public function getDelAddress()
    {
        if ($this->_oDelAddress === null) {
            $this->_oDelAddress = false;
            $oOrder = oxNew(Order::class);
            $this->_oDelAddress = $oOrder->getDelAddressInfo();
        }

        return $this->_oDelAddress;
    }

    /**
     * Returns true if there is a delivery address differing from invoice address.
     * @return bool|object
     * @throws SystemComponentException
     */
    protected function isAddressMismatch()
    {
        $delAddress = $this->getDelAddress();
        if (!$delAddress) {
            return false;
        }

        $user = $this->getUser();
        if (!$user) {
            return true;
        }

        return !$this->equalAddresses($user, $delAddress);
    }

    /**
     * Returns true if the delivery address is a foreign (non german) address. Also returns true if unable to determine
     * the country.
     *
     * @return bool
     * @throws SystemComponentException
     */
    protected function isForeignAddress()
    {
        /** @var User $user */
        $user = $this->getUser();

        $delAddress = $this->getDelAddress();
        if ($delAddress) {
            $countryId = $delAddress->oxaddress__oxcountryid->value;
        } else {
            if (!$user) {
                return true;
            }

            $countryId = $user->oxuser__oxcountryid->value;
        }

        $germanId = $user->getUserCountryId('de');

        return $countryId != $germanId;
    }

    /**
     * Returns true if the delivery address is a packstation address. Also returns true if unable to determine
     * the address.
     *
     * @return bool
     * @throws SystemComponentException
     */
    protected function isPackstation()
    {
        /** @var User $user */
        $user = $this->getUser();

        $delAddress = $this->getDelAddress();
        if ($delAddress) {
            $street = $delAddress->oxaddress__oxstreet->value;
            $streetnr = $delAddress->oxaddress__oxstreetnr->value;
        } else {
            if (!$user) {
                return true;
            }

            $street = $user->oxuser__oxstreet->value;
            $streetnr = $user->oxuser__oxstreetnr->value;
        }

        return EasyCreditHelper::hasPackstationFormat($street, $streetnr);
    }

    /**
     * Compares relevant fields for delivery
     * @param $user
     * @param $delAddress
     *
     * @return bool
     */
    protected function equalAddresses($user, $delAddress)
    {
        return ($user->oxuser__oxfname->value == $delAddress->oxaddress__oxfname->value
                && $user->oxuser__oxlname->value == $delAddress->oxaddress__oxlname->value
                && $user->oxuser__oxstreet->value == $delAddress->oxaddress__oxstreet->value
                && $user->oxuser__oxstreetnr->value == $delAddress->oxaddress__oxstreetnr->value
                && $user->oxuser__oxzip->value == $delAddress->oxaddress__oxzip->value
                && $user->oxuser__oxcity->value == $delAddress->oxaddress__oxcity->value
                && $user->oxuser__oxcountryid->value == $delAddress->oxaddress__oxcountryid->value
        );
    }

    /**
     * Validates oxidcreditcard and oxiddebitnote user payment data.
     * Returns null if problems on validating occured. If everything
     * is OK - returns "order" and redirects to payment confirmation
     * page.
     *
     * Session variables:
     * <b>paymentid</b>, <b>dynvalue</b>, <b>payerror</b>
     *
     * @return  mixed
     * @throws SystemComponentException
     */
    public function validatePayment()
    {
        /** @var Session $session */
        $session = Registry::getSession();

        if (!($sPaymentId = Registry::getConfig()->getRequestParameter('paymentid'))) {
            $sPaymentId = $session->getVariable('paymentid');
        }

        return $this->validateEasyCreditPayment($sPaymentId, $session);
    }

    protected function validateEasyCreditPayment($sPaymentId, $session)
    {
        if ($sPaymentId == $this->getApiConfig()->getEasyCreditInstalmentPaymentId()) {

            if (!$this->isEasyCreditPossible()) {
                $session->deleteVariable('paymentid');
                return;
            }

            try {
                $this->addProfileData();
            } catch (\Exception $ex) {
                $this->handleUserException($ex->getMessage());
                return;
            }

            return 'EasyCreditDispatcher?fnc=initializeandredirect';
        }

        return parent::validatePayment();
    }

    /**
     * Sets additional data delivered by easycredit payment form to current user profile
     */
    protected function addProfileData()
    {
        /** @var $user User */
        $user = $this->getUser();
        $profileData = $this->getConfig()->getRequestParameter('ecred', true);

        $hasChanged = false;

        $dateOfBirth = $this->getValidatedDateOfBirth($profileData, $user);
        if ($dateOfBirth) {
            $user->oxuser__oxbirthdate = new Field($dateOfBirth, Field::T_RAW);
            $hasChanged = true;
        }

        $salutation = $this->getValidatedSalutation($profileData);
        if ($salutation) {
            $user->oxuser__oxsal = new Field($salutation, Field::T_RAW);
            $hasChanged = true;
        }


        if ($hasChanged) {
            $user->save();
        }
    }

    /**
     * Gets agreement text which has to be accepted by user to use easyCredit
     *
     * @return string agreement text
     */
    public function getAgreementTxt()
    {
        if ($this->agreementTxt === false) {
            $this->agreementTxt = $this->loadAgreementTxt();
        }
        return $this->agreementTxt;
    }

    /**
     * Loads agreements by webservice
     *
     * @return string agreements or null
     */
    protected function loadAgreementTxt()
    {
        try {
            $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE, [$this->getWebshopId()]);
            return $response->zustimmungDatenuebertragungPaymentPage;
        } catch (\Exception $ex) {
        }
        return null;
    }

    /**
     * Calls webservice endpoint
     *
     * @var string $endpoint name of the service
     * @var array $additionalArguments args which can be used in url
     * @var array $queryArguments query args
     * @return string response
     * @throws \Exception if something happened
     */
    protected function call($endpoint, $additionalArguments = [], $queryArguments = [])
    {
        try {
            $webServiceClient = EasyCreditWebServiceClientFactory::getWebServiceClient($endpoint, $this->getDic(), $additionalArguments, $queryArguments);
            return $webServiceClient->execute();
        } catch (\Exception $ex) {
            $this->handleException($ex);
            throw $ex;
        }
    }

    /**
     * Returns true if there is some user profile data missing that is needed by easyCredit
     *
     * @return bool
     */
    public function isProfileDataMissing()
    {
        return !$this->hasSalutation();
    }

    /**
     * Returns true if user has valid salutation
     *
     * @return bool
     */
    public function hasSalutation()
    {
        $salutation = $this->getUser()->oxuser__oxsal->value;
        return $this->isValidSalutation($salutation);
    }

    /**
     * Returns true if is a valid salutation regarding easyCredit usage
     *
     * @return bool
     */
    protected function isValidSalutation($salutation)
    {
        return !empty($salutation) && ($salutation == "MR" || $salutation == "MRS");
    }

    /**
     * Logs and saves an exception
     *
     * @param \Exception $ex
     */
    private function handleException(\Exception $ex)
    {
        $errorMessage = $ex->getMessage();
        $this->getDic()->getLogging()->log($errorMessage);
        $this->addErrorMessage($errorMessage);
    }

    private function addErrorMessage($message)
    {
        $this->errorMessages[] = $message;
    }

    /**
     * Returns webshop id
     *
     * @return string webshopid
     */
    private function getWebshopId()
    {
        $apiConfig = $this->getApiConfig();
        return $apiConfig->getWebShopId();
    }

    /**
     * Returns dic apiconfig
     *
     * @return EasyCreditApiConfig
     */
    protected function getApiConfig()
    {
        return $this->getDic()->getApiConfig();
    }

    /**
     * Returns validated date of birth by requestdata
     *
     * @param $requestData array
     * @param $user User
     *
     * @return string date of birth
     * @throws EasyCreditException
     */
    protected function getValidatedDateOfBirth($requestData, $user)
    {
        $birthday = $requestData["oxuser__oxbirthdate"] ?? null;
        if (!empty($birthday) && is_array($birthday)) {
            $convertedBirthday = $user->convertBirthday($birthday);
            if ($convertedBirthday) {
                if (strtotime($convertedBirthday) > time()) {
                    throw new EasyCreditException("OXPS_EASY_CREDIT_ERROR_DATEOFBIRTH_INVALID");
                }
                return $convertedBirthday;
            }
        }
        return null;
    }

    /**
     * Returns validated salutation by requestdata
     *
     * @param $requestData array
     *
     * @return string salutation
     * @throws EasyCreditException
     */
    protected function getValidatedSalutation($requestData)
    {
        $salutation = $requestData["oxuser__oxsal"];
        if ($this->isValidSalutation($salutation)) {
            return $salutation;
        }
        return null;
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
}
