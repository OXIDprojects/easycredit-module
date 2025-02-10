<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Helper;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Groups;
use OxidEsales\Eshop\Application\Model\Manufacturer;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;

/**
 * Class to build the data for request "VorgangInitialisierenRequest" as part of initialization of easyCredit
 * This class is isolated and prepared to use in unit tests. For this case please set all members from outside.
 */
class EasyCreditInitializeRequestBuilder implements EasyCreditInitializeRequestBuilderInterface
{
    public const INTEGRATIONSART           = 'PAYMENT_PAGE';
    public const DEFAULT_INSTALMENT_TIME   = 36;
    public const RISCS_NO_INFO             = "KEINE_INFORMATION";
    public const RISCS_NEUKUNDE            = "NEUKUNDE";
    public const RISCS_BESTANDSKUNDE       = "BESTANDSKUNDE";
    public const ARTICLE_GTIN              = "GTIN";

    /** @var User */
    private $user;

    /** @var Basket */
    private $basket;

    /** @var Address */
    private $shippingAddress;

    /** @var string */
    private $shippingCountryIso;

    /** @var string */
    private $billingCountryIso;

    /** @var string */
    private $shopEdition;

    /** @var string */
    private $moduleVersion;

    /** @var  string */
    private $baseLanguage;

    /** @var EasyCreditDic */
    private $dic;

    private $salutationMapping = [
        "MR"    => "HERR",
        "MRS"   => "FRAU",
    ];

    /**
     * Builds and gets request body content for VorgangInitialisierenRequest
     *
     * @return array data
     */
    public function getInitializationData()
    {
        $initRequest = [
            'integrationsart'           => self::INTEGRATIONSART,
            'shopKennung'               => $this->getWebshopId(),
            'bestellwert'               => $this->getBasketPrice(),
            'laufzeit'                  => $this->getInstalmentTime(),
            'ruecksprungadressen'       => $this->getResponseUrls(),
            'rechnungsadresse'          => array_filter($this->convertBillingAddress()),
            'lieferadresse'             => array_filter($this->convertShippingAddress()),
            'personendaten'             => array_filter($this->getPersonals()),
            'kontakt'                   => $this->getContacts(),
            'weitereKaeuferangaben'     => array_filter($this->getFurtherCustomerInfo()),
            'risikorelevanteAngaben'    => $this->getRiscs(),
            'warenkorbinfos'            => array_filter($this->getBasketInfo()),
            'technischeShopparameter'   => array_filter($this->getTechnicals()),
            'VorgangskennungShop'       => $this->getOrderNr(),
        ];

        return array_filter($initRequest);
    }

    /**
     * Returns oxid eShop system info
     *
     * @return string
     */
    protected function getShopSystem()
    {
        return $this->shopEdition;
    }

    /**
     * Returns easyCredit module version
     *
     * @return string
     */
    protected function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     * Returns "warenkorbinfos"
     *
     * @return array
     */
    protected function getBasketInfo()
    {
        $basketInfo = [];

        $basketitemlist = $this->getBasket()->getBasketArticles();
        $basketContents = $this->getBasket()->getContents();
        if (empty($basketContents)) {
            return $basketInfo;
        }

        foreach ($basketContents as $basketindex => $basketitem) {
            $basketproduct = $basketitemlist[$basketindex];
            $basketInfo[] = $this->getBasketPositionInfo($basketitem, $basketproduct);
        }
        return $basketInfo;
    }

    /**
     * Returns "OrderNr"
     *
     * @return string
     */
    protected function getOrderNr()
    {
        $orderNr = '';
        if ($orderId = $this->getBasket()->getOrderId()) {
            $order = oxNew(Order::class);
            $order->load($orderId);
            $orderNr = $order->oxorder__oxordernr->value;
        }
        return $orderNr;
    }

    /**
     * Returns "risikorelevanteAngaben"
     *
     * @return array
     */
    protected function getRiscs()
    {
        $user = $this->getUser();

        $isGuestOrder = empty($user->oxuser__oxpassword->value);
        if ($isGuestOrder) {
            $registerDate = "";
            $customerStatus = self::RISCS_NEUKUNDE;
        } else { //registered user
            $registerDate = $this->getDate($user->oxuser__oxregister->value);

            $customerStatus = $this->getRegisteredCustomerStatus($user);
        }

        $basket = $this->getBasket();
        return [
            "bestellungErfolgtUeberLogin" => !$isGuestOrder,
            "kundeSeit" => $registerDate,
            "anzahlBestellungen" => $user->getOrderCount(),
            "kundenstatus" => $customerStatus,
            "anzahlProdukteImWarenkorb" => $basket->getItemsCount(),
            "negativeZahlungsinformation" => self::RISCS_NO_INFO,
            "risikoartikelImWarenkorb" => false,
            "logistikDienstleister" => "",
        ];
    }

    protected function getRegisteredCustomerStatus($user)
    {
        self::RISCS_BESTANDSKUNDE;
        $userGroups = $user->getUserGroups();
        if (count($userGroups)) {
            /** @var $userGroup Groups */
            foreach ($userGroups as $userGroup) {
                if ($userGroup->getId() == "oxidnotyetordered") {
                    return self::RISCS_NEUKUNDE;
                }
            }
        }
    }

    /**
     * Returns customer salutation
     *
     * @return string|null
     */
    protected function getSalutation()
    {
        $salutation = $this->getUser()->oxuser__oxsal->value;
        if ($salutation) {
            if (key_exists($salutation, $this->salutationMapping)) {
                return $this->salutationMapping[$salutation];
            }
        }
        return null;
    }

    /**
     * Return birthday in proper format
     *
     * @return false|null|string
     */
    protected function convertBirthday()
    {
        $birthday = $this->getUser()->oxuser__oxbirthdate->value;
        if ($birthday && $birthday != "0000-00-00") {
            return $this->getDate($birthday);
        }
        return null;
    }

    /**
     * Returns customer billing address
     *
     * @return array
     */
    protected function getBillingAddress()
    {

        $user = $this->getUser();

        $countryIso2 = $this->getBillingCountryIso2($user->oxuser__oxcountryid->value);
        $fullStreet = $this->getFullStreet($user->oxuser__oxstreet->value, $user->oxuser__oxstreetnr->value);

        $address = [
            "strasseHausNr" => $fullStreet,
            "adresszusatz" => $user->oxuser__oxaddinfo->value,
            "plz" => $user->oxuser__oxzip->value,
            "ort" => $user->oxuser__oxcity->value,
            "land" => $countryIso2,
        ];

        return $address;
    }

    /**
     * Returns customer shipping address
     *
     * @return array
     */
    protected function convertShippingAddress()
    {

        $user = $this->getUser();
        $address = [
            "vorname" => $user->oxuser__oxfname->value,
            "nachname" => $user->oxuser__oxlname->value,
            "packstation" => false,
        ];

        $delivadr = $this->getShippingAddress();
        if ($delivadr) {
            $countryIso2 = $this->getShippingCountryIso($delivadr->oxaddress__oxcountryid->value);
            $street = $delivadr->oxaddress__oxstreet->value;
            $streetNr = $delivadr->oxaddress__oxstreetnr->value;
            $fullStreet = $this->getFullStreet($street, $streetNr);
            $address = [
                "vorname" => $delivadr->oxaddress__oxfname->value,
                "nachname" => $delivadr->oxaddress__oxlname->value,
                "strasseHausNr" => $fullStreet,
                "adresszusatz" => $delivadr->oxaddress__oxaddinfo->value,
                "plz" => $delivadr->oxaddress__oxzip->value,
                "ort" => $delivadr->oxaddress__oxcity->value,
                "land" => $countryIso2,
                "packstation" => EasyCreditHelper::hasPackstationFormat($street, $streetNr),
            ];
        } else {
            $address = array_merge($address, $this->getBillingAddress());
        }

        return $address;
    }

    /**
     * Returns iso2 value of billing country
     *
     * @param string $countryId country id
     * @return string iso2
     */
    protected function getBillingCountryIso2($countryId)
    {
        if (!$this->billingCountryIso) {
            $this->billingCountryIso = $this->getCountryIso2ByCountryId($countryId);
        }
        return $this->billingCountryIso;
    }

    /**
     * Returns iso2 country code of shipping country
     *
     * @param string $countryId country id
     * @return string
     */
    protected function getShippingCountryIso($countryId)
    {
        if (!$this->shippingCountryIso) {
            $this->shippingCountryIso = $this->getCountryIso2ByCountryId($countryId);
        }
        return $this->shippingCountryIso;
    }

    /**
     * Returns full street name
     *
     * @param $street
     * @param $streetNo
     *
     * @return string
     */
    private function getFullStreet($street, $streetNo)
    {
        if ($streetNo) {
            return $street . " " . $streetNo;
        }
        return $street;
    }

    /**
     * Returns SUCCESS url
     *
     * @return string
     */
    protected function getSuccessUrl()
    {
        $successUrl = $this->getBaseUrl() . "&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails";
        return $this->getSession()->processUrl($successUrl);
    }

    /**
     * Returns cancel url
     *
     * @return string
     */
    protected function getAbortUrl()
    {
        $abortUrl = $this->getBaseUrl() . "&cl=payment"; //TODO schow error
        return $this->getSession()->processUrl($abortUrl);
    }

    /**
     * Return reject url
     *
     * @return string
     */
    protected function getRejectUrl()
    {
        $rejectUrl = $this->getBaseUrl() . "&cl=payment";
        return $this->getSession()->processUrl($rejectUrl);
    }

    /**
     * Returns base part of oxid eShop url
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        $url = $this->getConfig()->getSslShopUrl();
        $url .= "index.php?lang=" . $this->getBaseLanguage();
        $url .= "&sid=" . $this->getSession()->getId();
        $url .= "&shp=" . $this->getConfig()->getShopId();

        return $url;
    }

    /**
     * Returns session
     *
     * @return Session
     */
    protected function getSession()
    {

        $session = $this->getDic()->getSession();
        return $session;
    }

    /**
     * Returns config
     *
     * @return Config
     */
    protected function getConfig()
    {

        $config = $this->getDic()->getConfig();
        return $config;
    }

    /**
     * Returns webshop id
     *
     * @return string
     */
    protected function getWebshopId()
    {
        return $this->getApiConfig()->getWebShopId();
    }

    /**
     * Returns instalment time
     *
     * @return int
     */
    protected function getInstalmentTime()
    {

        return self::DEFAULT_INSTALMENT_TIME;
    }

    /**
     * Returns basket
     *
     * @return Basket
     */
    protected function getBasket()
    {

        return $this->basket;
    }

    /**
     * Returns billing address
     *
     * @return array
     */
    protected function convertBillingAddress()
    {
        return $this->getBillingAddress();
    }

    /**
     * Returns shipping address
     *
     * @return object oxaddress
     */
    protected function getShippingAddress()
    {

        return $this->shippingAddress;
    }

    /**
     * Returns user
     *
     * @return User
     */
    protected function getUser()
    {

        return $this->user;
    }

    /**
     * Sets shop edition
     *
     * @param string $shopEdition
     */
    public function setShopEdition($shopEdition)
    {

        $this->shopEdition = $shopEdition;
    }

    /**
     * Sets user/customer
     *
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Sets basket
     *
     * @param Basket $basket
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @param string $shippingCountryIso
     * @codeCoverageIgnore
     */
    public function setShippingCountryIso($shippingCountryIso)
    {
        $this->shippingCountryIso = $shippingCountryIso;
    }

    /**
     * Sets iso2 of billing country (NOT MUST!)
     * It is important only for unit testing
     *
     * @param string $billingCountryIso
     * @codeCoverageIgnore
     */
    public function setBillingCountryIso($billingCountryIso)
    {
        $this->billingCountryIso = $billingCountryIso;
    }

    /**
     * Sets module version
     *
     * @param string $moduleVersion
     */
    public function setModuleVersion($moduleVersion)
    {
        $this->moduleVersion = $moduleVersion;
    }

    private function getCountryIso2ByCountryId($countryId)
    {
        $country = oxNew(Country::class);
        if ($country->load($countryId)) {
            return $country->oxcountry__oxisoalpha2->value;
        }
        return "";
    }

    /**
     * Returns response urls
     *
     * @return array
     */
    protected function getResponseUrls()
    {
        return [
            'urlAbbruch'   => $this->getAbortUrl(),
            'urlErfolg'    => $this->getSuccessUrl(),
            'urlAblehnung' => $this->getRejectUrl(),
        ];
    }

    /**
     * Returns personal date of customer
     *
     * @return array
     */
    protected function getPersonals()
    {
        $user = $this->getUser();
        $this->validateUser($user);
        return [
            'anrede'       => $this->getSalutation(),
            'vorname'      => $user->oxuser__oxfname->value,
            'nachname'     => $user->oxuser__oxlname->value,
            'geburtsdatum' => $this->convertBirthday(),
        ];
    }

    private function validateUser($user)
    {
        $fname = $user->oxuser__oxfname->value;
        $lname = $user->oxuser__oxlname->value;
        $fnameLength = strlen($fname);
        $lnameLength = strlen($lname);

        if ($fnameLength < 2) {
            throw new EasyCreditException('OXPS_EASY_CREDIT_ERROR_FNAME_SMALL');
        }

        if ($fnameLength > 27) {
            throw new EasyCreditException('OXPS_EASY_CREDIT_ERROR_FNAME_LONG');
        }

        if ($lnameLength < 2) {
            throw new EasyCreditException('OXPS_EASY_CREDIT_ERROR_LNAME_SMALL');
        }

        if ($lnameLength > 27) {
            throw new EasyCreditException('OXPS_EASY_CREDIT_ERROR_LNAME_LONG');
        }

        // Define the allowed character set
        $pattern = '/[^-a-zÀ-žA-ZäüößÄÖÜěščřžůďťňĎŇŤŠČŘŽŮĚO\'\.\, ]/';

        // Check for invalid characters in first name
        if (preg_match_all($pattern, $fname, $matches)) {
            $invalidChars = implode(', ', array_unique($matches[0]));
            $exceptionMessage = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_FNAME_CONTAINS_INVALID_CHAR');
            throw new EasyCreditException(sprintf($exceptionMessage, $invalidChars));
        }

        // Check for invalid characters in last name
        if (preg_match_all($pattern, $lname, $matches)) {
            $invalidChars = implode(', ', array_unique($matches[0]));
            $exceptionMessage = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_LNAME_CONTAINS_INVALID_CHAR');
            throw new EasyCreditException(sprintf($exceptionMessage, $invalidChars));
        }
    }

    /**
     * Returns contact information
     *
     * @return array
     */
    protected function getContacts()
    {
        $customer = $this->getUser();
        $contacts = [
            'email' => $customer->oxuser__oxusername->value,
        ];
        $phoneNumber = $customer->oxuser__oxfon->value;
        if ($this->isValidPhoneNumber($phoneNumber)) {
            $contacts["mobilfunknummer"] = $phoneNumber;
            $contacts["pruefungMobilfunknummerUebergehen"] = true;
        }
        return $contacts;
    }

    /**
     * Determines if phone number is valid
     *
     * @param $phoneNumber
     *
     * @return boolean
     */
    protected function isValidPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return false;
        }
        return preg_match('/^[\+]?[\d \- ]+$/', $phoneNumber); //leading +, then numbers, minus and spaces
    }

    /**
     * Returns further information about customer
     *
     * @return array
     */
    protected function getFurtherCustomerInfo()
    {
        $customer = $this->getUser();
        return [
            'telefonnummer' => $customer->oxuser__oxfon->value,
        ];
    }

    /**
     * Returns information about shop system
     *
     * @return array
     */
    protected function getTechnicals()
    {
        return [
            'shopSystemHersteller'   => "OXID eShop " . $this->getShopSystem(),
            'shopSystemModulversion' => $this->getModuleVersion(),
        ];
    }

    /**
     * Returns total basket price
     *
     * @return double
     */
    protected function getBasketPrice()
    {
        $basket = $this->getBasket();
        return $basket->getPrice()->getPrice();
    }

    /**
     * Returns information about an certain basket position
     *
     * @param $basketitem BasketItem
     * @param $basketproduct
     *
     * @return array
     */
    protected function getBasketPositionInfo($basketitem, $basketproduct)
    {
        /** @var $article Article */
        $article = $basketitem->getArticle();

        $manufacturerTitle = "";
        /** @var $manufacturer Manufacturer */
        $manufacturer = $article->getManufacturer();
        if ($manufacturer && $manufacturer->getId()) {
            $manufacturerTitle = $manufacturer->getTitle();
        }

        $categoryTitle = "";
        /** @var $category Category */
        $category = $article->getCategory();
        if ($category && $category->getId()) {
            $categoryTitle = $category->getTitle();
        }

        $price = "";
        $unitPrice = $basketitem->getUnitPrice();
        if ($unitPrice) {
            $price = $unitPrice->getPrice();
        }

        return [
            "produktbezeichnung" => $basketitem->getTitle(),
            "menge"              => $basketitem->getAmount(),
            "preis"              => $price,
            "hersteller"         => $manufacturerTitle,
            "produktkategorie"   => $categoryTitle,
            "artikelnummern"     => [
                [
                    "nummerntyp" => self::ARTICLE_GTIN,
                    "nummer"     => $basketproduct->oxarticles__oxartnum->value,
                ],
            ],
        ];
    }

    /**
     * Returns date without time component
     * @param $date
     *
     * @return false|null|string
     */
    protected function getDate($date)
    {
        if (empty($date) || $date < 1) {
            return "";
        }

        if (strtotime($date) === false) {
            return "";
        }

        return date('Y-m-d', strtotime($date));
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
     * @return string
     */
    protected function getBaseLanguage()
    {
        return $this->baseLanguage;
    }

    /**
     * @param string $baseLanguage
     */
    public function setBaseLanguage($baseLanguage)
    {
        $this->baseLanguage = $baseLanguage;
    }

    /**
     * Return paymenthash for this request data
     *
     * @param $initializationData array
     *
     * @return string
     */
    public static function generatePaymentHash($initializationData)
    {
        $paymentHash = md5(json_encode($initializationData));
        return $paymentHash;
    }
}
