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
 * @copyright (C) OXID eSales AG 2003-2021
 */

namespace OxidSolutionCatalysts\EasyCredit\Controller;

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
use OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDic;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Exception\EasyCreditException;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditHelper;

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
    private $easyCreditInstallmentPossible;

    private $easyCreditInvoicePossible;

    /** @var string */
    private $installmentAgreementTxt = false;

    private $invoiceAgreementTxt = false;

    /**
     * Returns the dic container.
     *
     * @return EasyCreditDic
     * @throws SystemComponentException
     */
    public function getDic()
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
    public function getBasket()
    {
        return Registry::getSession()->getBasket();
    }

    /**
     * Checks if ratenkauf is a valid payment in this checkout process. If not, false is returned.
     *
     * @return bool|\\stdClass
     * @throws SystemComponentException
     */
    public function isEasyCreditInstallmentPossible()
    {
        if ($this->easyCreditInstallmentPossible === null) {
            $this->checkEasyCreditInstallmentPossible();
        }
        return $this->easyCreditInstallmentPossible;
    }

    /**
     * Checks if ratenkauf is a valid payment in this checkout process. If not, false is returned.
     *
     * @return bool|\\stdClass
     * @throws SystemComponentException
     */
    public function isEasyCreditInvoicePossible()
    {
        if ($this->easyCreditInvoicePossible === null) {
            $this->checkEasyCreditInvoicePossible();
        }
        return $this->easyCreditInvoicePossible;
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditInstallmentPossible()
    {
        $this->easyCreditInstallmentPossible = true;

        $this->checkEasyCreditForeignAddress();

        $this->checkEasyCreditAddressMismatch();

        $this->checkEasyCreditPackstation();

        $this->checkEasyCreditInstallmentAgreementTxt();

        $this->checkEasyCreditExampleCalulation();
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditInvoicePossible()
    {
        $this->easyCreditInvoicePossible = true;

        $this->checkEasyCreditForeignAddress();

        $this->checkEasyCreditAddressMismatch();

        $this->checkEasyCreditPackstation();

        $this->checkEasyCreditInvoiceAgreementTxt();
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditForeignAddress()
    {
        if ($this->isForeignAddress()) {
            $this->errorMessages[] = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_NO_GERMAN_ADDRESS');
            $this->easyCreditInstallmentPossible = false;
            $this->easyCreditInvoicePossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditAddressMismatch()
    {
        if ($this->isAddressMismatch()) {
            $this->errorMessages[] = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_DEL_ADDRESS');
            $this->easyCreditInstallmentPossible = false;
            $this->easyCreditInvoicePossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditPackstation()
    {
        if ($this->isPackstation()) {
            $this->errorMessages[] = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_PACKSTATION');
            $this->easyCreditInstallmentPossible = false;
            $this->easyCreditInvoicePossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditInstallmentAgreementTxt()
    {
        $agreements = $this->getInstallmentAgreementTxt();
        if (empty($agreements)) {
            $this->errorMessages[] = Registry::getLang()->translateString('OXPS_EASY_CREDIT_INSTALLMENT_ERROR_NO_AGREEMENTS');
            $this->easyCreditInstallmentPossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditInvoiceAgreementTxt()
    {
        $agreements = $this->getInvoiceAgreementTxt();
        if (empty($agreements)) {
            $this->errorMessages[] = Registry::getLang()->translateString('OXPS_EASY_CREDIT_INVOICE_ERROR_NO_AGREEMENTS');
            $this->easyCreditInvoicePossible = false;
        }
    }

    /**
     * Part of valid payment check
     */
    protected function checkEasyCreditExampleCalulation()
    {
        $response = $this->getExampleCalulation();
        if (is_string($response)) {
            $this->errorMessages[] = $response;
            $this->easyCreditInstallmentPossible = false;
        } else {
            $this->easyCreditInstallmentPossible = $this->easyCreditInstallmentPossible && true;
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
            (int)$price->getBruttoPrice() < (int)$payment->getFieldData('oxfromamount') ||
            (int)$price->getBruttoPrice() > (int)$payment->getFieldData('oxtoamount')
        ) {
            return false;
        }

        try {
            if ($this->getApiConfig()->getEasyCreditUseApiVersionV3()) {
                return $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V3_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN, [],[$this->getApiConfig()->getWebShopId()]);
            } else {
                return $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN, [], [EasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_FINANZIERUNGSBETRAG => $price->getBruttoPrice()]);
            }
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
     * @return Price
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

        if (!($sPaymentId = Registry::getRequest()->getRequestParameter('paymentid'))) {
            $sPaymentId = $session->getVariable('paymentid');
        }

        return $this->validateEasyCreditPayment($sPaymentId, $session);
    }

    protected function validateEasyCreditPayment($sPaymentId, $session)
    {
        if ($sPaymentId == $this->getApiConfig()->getEasyCreditInstallmentPaymentId()) {
            if (!$this->isEasyCreditInstallmentPossible()) {
                $session->deleteVariable('paymentid');
                if (!empty($this->errorMessages)) {
                    foreach ($this->errorMessages as $message) {
                        $this->handleUserException($message);
                    }
                }
                return;
            }

            try {
                $this->addProfileData();
            } catch (\Exception $ex) {
                $this->handleUserException($ex->getMessage());
                return;
            }
            return 'EasyCreditDispatcher?fnc=initializeandredirectInstallment&stoken=' . Registry::getSession()->getSessionChallengeToken();
        }

        if ($sPaymentId == $this->getApiConfig()->getEasyCreditInvoicePaymentId()) {
            if (!$this->isEasyCreditInvoicePossible()) {
                $session->deleteVariable('paymentid');
                if (!empty($this->errorMessages)) {
                    foreach ($this->errorMessages as $message) {
                        $this->handleUserException($message);
                    }
                }
                return;
            }

            try {
                $this->addProfileData();
            } catch (\Exception $ex) {
                $this->handleUserException($ex->getMessage());
                return;
            }
            return 'EasyCreditDispatcher?fnc=initializeandredirectInvoice&stoken=' . Registry::getSession()->getSessionChallengeToken();
        }
        
        return parent::validatePayment();
    }

    /**
     * Sets additional data delivered by easycredit-module payment form to current user profile
     */
    protected function addProfileData()
    {
        /** @var $user User */
        $user = $this->getUser();
        $profileData = Registry::getRequest()->getRequestParameter('ecred', true);

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
    public function getInstallmentAgreementTxt()
    {
        if ($this->installmentAgreementTxt === false) {
            $this->installmentAgreementTxt = $this->loadInstallmentAgreementTxt();
        }
        return $this->installmentAgreementTxt;
    }

    /**
     * Gets agreement text which has to be accepted by user to use easyCredit
     *
     * @return string agreement text
     */
    public function getInvoiceAgreementTxt()
    {
        if ($this->invoiceAgreementTxt === false) {
            $this->invoiceAgreementTxt = $this->loadInvoiceAgreementTxt();
        }
        return $this->invoiceAgreementTxt;
    }

    /**
     * Loads agreements by webservice
     *
     * @return string agreements or null
     */
    protected function loadInstallmentAgreementTxt()
    {
        try {
            if ($this->getApiConfig()->getEasyCreditUseApiVersionV3()) {
                $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V3_ZUSTIMMUNGSTEXTE, [$this->getWebshopId()]);
                return $response->declarationOfConsent;
            } else {
                $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE, [$this->getWebshopId()]);
                return $response->zustimmungDatenuebertragungPaymentPage;
            }
        } catch (\Exception $ex) {
            Registry::getLogger()->error('EasyCredit loadInstallmentAgreementTxt failed: ' . $ex->getMessage(), ['exception' => $ex]);
        }
        return null;
    }

    /**
     * Loads agreements by webservice
     *
     * @return string agreements or null
     */
    protected function loadInvoiceAgreementTxt()
    {
        try {
            if ($this->getApiConfig()->getEasyCreditUseApiVersionV3()) {
                $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V3_ZUSTIMMUNGSTEXTE, [$this->getWebshopId()]);
                return $response->privacyApprovalForm;
            } else {
                $response = $this->call(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE, [$this->getWebshopId()]);
                return $response->zustimmungDatenuebertragungPaymentPage;
            }
        } catch (\Exception $ex) {
            Registry::getLogger()->error('EasyCredit loadInvoiceAgreementTxt failed: ' . $ex->getMessage(), ['exception' => $ex]);
        }
        return null;
    }

    /**
     * Calls webservice endpoint
     *
     * @return string response
     * @throws \Exception if something happened
     * @var array $queryArguments query args
     * @var string $endpoint name of the service
     * @var array $additionalArguments args which can be used in url
     */
    protected function call($endpoint, $additionalArguments = [], $queryArguments = [])
    {
        try {
            if ($this->getApiConfig()->getEasyCreditUseApiVersionV3()) {
                $webServiceClient = EasyCreditWebServiceClientFactory::getWebServiceClient(
                    $endpoint,
                    $this->getDic(),
                    $additionalArguments,
                    $queryArguments,
                    true
                );
                return $webServiceClient->executeJsonRequest('GET', $webServiceClient->getFunction());
            } else {
                $webServiceClient = EasyCreditWebServiceClientFactory::getWebServiceClient(
                    $endpoint,
                    $this->getDic(),
                    $additionalArguments,
                    $queryArguments
                );
                return $webServiceClient->execute();
            }
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
    public function getValidatedDateOfBirth($requestData, $user)
    {
        $birthday = $requestData["oxuser__oxbirthdate"] ?? false;
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
    public function getValidatedSalutation($requestData)
    {
        $salutation = $requestData["oxuser__oxsal"] ?? false;
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

    /**
     * @return boolean
     */
    public function isEasyCreditAPIV3(): bool
    {
        return $this->getApiConfig()->getEasyCreditUseApiVersionV3(); 
    }

    public function getPaymentList()
    {
        $paymentList = parent::getPaymentList();
        if (!$this->isEasyCreditAPIV3()) {
            unset($paymentList[$this->getApiConfig()->getEasyCreditInvoicePaymentId()]);
        }
        return $paymentList;
    }
}