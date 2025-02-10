<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Helper;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;

/**
 * Interface EasyCreditInitializeRequestBuilderInterface
 *
 * Builds data used by request to initialize new order process
 */
interface EasyCreditInitializeRequestBuilderInterface
{
    /**
     * Builds and gets request body content for VorgangInitialisierenRequest
     *
     * @return array data
     */
    public function getInitializationData();

    /**
     * Sets shop edition
     *
     * @param string $shopEdition
     */
    public function setShopEdition($shopEdition);

    /**
     * Sets user/customer
     *
     * @param User $user
     */
    public function setUser($user);

    /**
     * Sets basket
     *
     * @param Basket $basket
     */
    public function setBasket($basket);

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress($shippingAddress);

    /**
     * @param string $shippingCountryIso
     */
    public function setShippingCountryIso($shippingCountryIso);

    /**
     * Sets iso2 of billing country (NOT MUST!)
     * It is important only for unit testing
     *
     * @param string $billingCountryIso
     */
    public function setBillingCountryIso($billingCountryIso);

    /**
     * Sets module version
     *
     * @param string $moduleVersion
     */
    public function setModuleVersion($moduleVersion);

    /**
     * @param string $baseLanguage
     */
    public function setBaseLanguage($baseLanguage);

    /**
     * Return paymenthash for this request data
     *
     * @param $initializationData array
     *
     * @return string
     */
    public static function generatePaymentHash($initializationData);
}
