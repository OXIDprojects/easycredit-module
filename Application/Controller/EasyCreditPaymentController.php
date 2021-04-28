<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       easycredit
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidProfessionalServices\EasyCredit\Application\Controller;


/**
 * Class oxpsEasyCreditPayment.
 * Extends Payment.
 *
 * @see Payment
 */
class EasyCreditPaymentController extends EasyCreditPayment_parent
{
    /** @var oxpsEasyCreditDic */
    private $dic;

    /** @var stdClass */
    private $exampleCalculation;

    /** @var array */
    private $errorMessages;

    /** @var object */
    protected $_oDelAddress;

    /** @var null|bool|stdClass */
    private $easyCreditPossible;

    /** @var string */
    private $agreementTxt = false;

    /**
     * Returns the dic container.
     *
     * @return oxpsEasyCreditDic
     * @throws oxSystemComponentException
     */
    protected function getDic()
    {
        if (!$this->dic) {
            $this->dic = oxpsEasyCreditDicFactory::getDic();
        }

        return $this->dic;
    }

    /**
     * Returns active basket
     *
     * @return oxBasket
     */
    protected function getBasket()
    {
        return oxRegistry::getSession()->getBasket();
    }

    /**
     * Returns true if easyCredit is allowed by aquisition value.
     * Checks before whether aqusisition value has to be updated
     *
     * @return bool true, if easyCredit is allowed
     */
    public function isEasyCreditPermitted()
    {
        /** @var $aquisitionBorder oxpsEasyCreditAquisitionBorder */
        $aquisitionBorder = oxNew("oxpsEasyCreditAquisitionBorder");
        $aquisitionBorder->updateAquisitionBorderIfNeeded();

        /** @var $aquisitionBorder oxpsEasyCreditAquisitionBorder */
        $aquisitionBorder = oxNew("oxpsEasyCreditAquisitionBorder");

        if(!$aquisitionBorder->considerInFrontend() ) {
            return true;
        }

        $squisitionBorderValue = $aquisitionBorder->getCurrentAquisitionBorderValue();
        if( empty($squisitionBorderValue) ) {
            return true;
        }
        $basketPrice = $this->getBasket()->getPrice()->getPrice();
        return $basketPrice && $squisitionBorderValue > $basketPrice;
    }

    /**
     * Checks if ratenkauf is a valid payment in this checkout process. If not, false is returned.
     *
     * @return bool|stdClass
     * @throws oxSystemComponentException
     */
    public function isEasyCreditPossible()
    {
        if ($this->easyCreditPossible === null) {
            $this->checkEasyCreditPossible();
        }

        return $this->easyCreditPossible;
    }

    protected function checkEasyCreditPossible()
    {
        $this->easyCreditPossible = true;

        $this->checkEasyCreditPermitted();

        $this->checkEasyCreditForeignAddress();

        $this->checkEasyCreditAddressMismatch();

        $this->checkEasyCreditPackstation();

        $this->checkEasyCreditAgreementTxt();

        $this->checkEasyCreditExampleCalulation();
    }

    protected function checkEasyCreditPermitted()
    {
        if(!$this->isEasyCreditPermitted() ) {
            $this->errorMessages[]    = oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_NOT_ALLOWED_BY_AQUISITION_VALUE');
            $this->easyCreditPossible = false;
        }
    }

    protected function checkEasyCreditForeignAddress()
    {
        if ($this->isForeignAddress()) {
            $this->errorMessages[]    = oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_NO_GERMAN_ADDRESS');
            $this->easyCreditPossible = false;
        }
    }

    protected function checkEasyCreditAddressMismatch()
    {
        if ($this->isAddressMismatch()) {
            $this->errorMessages[]    = oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_DEL_ADDRESS');
            $this->easyCreditPossible = false;
        }
    }

    protected function checkEasyCreditPackstation()
    {
        if ($this->isPackstation()) {
            $this->errorMessages[]    = oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_PACKSTATION');
            $this->easyCreditPossible = false;
        }
    }

    protected function checkEasyCreditAgreementTxt()
    {
        $agreements = $this->getAgreementTxt();
        if( empty($agreements)) {
            $this->errorMessages[]    = oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_NO_AGREEMENTS');
            $this->easyCreditPossible = false;
        }
    }

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
     * @return bool|stdClass
     * @throws oxSystemComponentException
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
     * @return bool|stdClass
     * @throws oxSystemComponentException
     */
    protected function getExampleCalculationResponse() {
        $price = $this->getPrice();
        if (!$price) {
            return false;
        }

        try {
            return $this->call( oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN
                , array()
                , array(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_FINANZIERUNGSBETRAG => $price->getBruttoPrice()));
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Returns the price relevant for the example calculation.
     *
     * @return oxPrice
     * @throws oxSystemComponentException
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
     * @return oxPrice
     * @throws oxSystemComponentException
     */
    public function getExampleCalculationPrice($articleId)
    {
        if ($articleId) {
            /** @var oxArticle $article */
            $article = oxNew('oxarticle');
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
     * @throws oxSystemComponentException
     */
    public function getDelAddress()
    {
        if ($this->_oDelAddress === null) {
            $this->_oDelAddress = false;
            $oOrder = oxNew('oxorder');
            $this->_oDelAddress = $oOrder->getDelAddressInfo();
        }

        return $this->_oDelAddress;
    }

    /**
     * Returns true if there is a delivery address differing from invoice address.
     * @return bool|object
     * @throws oxSystemComponentException
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
     * @throws oxSystemComponentException
     */
    protected function isForeignAddress()
    {
        /** @var oxUser $user */
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
     * @throws oxSystemComponentException
     */
    protected function isPackstation()
    {
        /** @var oxUser $user */
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

        return oxpsEasyCreditHelper::hasPackstationFormat($street, $streetnr);
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
     * @throws oxSystemComponentException
     */
    public function validatePayment()
    {
        /** @var oxSession $session */
        $session = oxRegistry::getSession();

        if (!($sPaymentId = oxRegistry::getConfig()->getRequestParameter('paymentid'))) {
            $sPaymentId = $session->getVariable('paymentid');
        }

        return $this->validateEasyCreditPayment($sPaymentId, $session);
    }

    protected function validateEasyCreditPayment($sPaymentId, $session)
    {
        if ($sPaymentId == $this->getApiConfig()->getEasyCreditInstalmentPaymentId()) {

            if(!$this->isEasyCreditPossible()) {
                $session->deleteVariable('paymentid');
                return;
            }

            try {
                $this->addProfileData();
            }
            catch(Exception $ex) {
                $this->handleUserException($ex->getMessage());
                return;
            }

            return 'oxpsEasyCreditDispatcher?fnc=initializeandredirect';
        }

        return parent::validatePayment();
    }

    /**
     * Sets additional data delivered by easycredit payment form to current user profile
     */
    protected function addProfileData()
    {
        /** @var $user oxUser */
        $user = $this->getUser();
        $profileData = $this->getConfig()->getRequestParameter('ecred', true);

        $hasChanged = false;

        $dateOfBirth = $this->getValidatedDateOfBirth($profileData, $user);
        if( $dateOfBirth ) {
            $user->oxuser__oxbirthdate = new oxField($dateOfBirth, oxField::T_RAW);
            $hasChanged = true;
        }

        $salutation = $this->getValidatedSalutation($profileData);
        if( $salutation ) {
            $user->oxuser__oxsal = new oxField($salutation, oxField::T_RAW);
            $hasChanged = true;
        }


        if( $hasChanged ) {
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
        if( $this->agreementTxt === false ) {
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
            $response = $this->call(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE, array($this->getWebshopId()));
            return $response->zustimmungDatenuebertragungPaymentPage;
        }
        catch(Exception $ex) {}
        return null;
    }

    /**
     * Calls webservice endpoint
     *
     * @var string $endpoint name of the service
     * @var array $additionalArguments args which can be used in url
     * @var array $queryArguments query args
     * @return string response
     * @throws Exception if something happened
     */
    protected function call($endpoint, $additionalArguments = array(), $queryArguments = array())
    {
        try {
            $webServiceClient = oxpsEasyCreditWebServiceClientFactory::getWebServiceClient($endpoint
                , $this->getDic()
                , $additionalArguments
                , $queryArguments);
            return $webServiceClient->execute();
        } catch (Exception $ex) {
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
        return !$this->hasBirthday() || !$this->hasSalutation();
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
     * Is date of in user profile?
     *
     * @return bool
     */
    public function hasBirthday()
    {
        $birthday = $this->getUser()->oxuser__oxbirthdate->value;
        return $birthday && $birthday != "0000-00-00";
    }

    /**
     * Logs and saves an exception
     *
     * @param Exception $ex
     */
    private function handleException(Exception $ex)
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
     * @return oxpsEasyCreditApiConfig
     */
    protected function getApiConfig()
    {
        return $this->getDic()->getApiConfig();
    }

    /**
     * Returns validated date of birth by requestdata
     *
     * @param $requestData array
     * @param $user oxUser
     *
     * @return string date of birth
     * @throws oxpsEasyCreditException
     */
    protected function getValidatedDateOfBirth($requestData, $user)
    {
        $birthday = $requestData["oxuser__oxbirthdate"];
        if (!empty($birthday) && is_array($birthday)) {
            $convertedBirthday = $user->convertBirthday($birthday);
            if ($convertedBirthday) {
                if (strtotime($convertedBirthday) > time()) {
                    throw new oxpsEasyCreditException("OXPS_EASY_CREDIT_ERROR_DATEOFBIRTH_INVALID");
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
     * @throws oxpsEasyCreditException
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
        $oEx = oxNew('oxExceptionToDisplay');
        $oEx->setMessage($i18nMessage);
        oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
    }
}