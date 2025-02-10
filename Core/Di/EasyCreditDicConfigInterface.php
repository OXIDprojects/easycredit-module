<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Di;

/**
 * Interface DicConfigInterface
 *
 * Providing a interface for http and unittest.
 */
interface EasyCreditDicConfigInterface
{
    /**
     * Returns active shop ID.
     *
     * @return int
     */
    public function getShopId();

    /**
     * Returns config sSSLShopURL or sMallSSLShopURL if secondary shop
     *
     * @param int $iLang language (default is null)
     *
     * @return string
     */
    public function getSslShopUrl($iLang = null);

    /**
     * Returns config parameter value if such parameter exists
     *
     * @param string $sName config parameter name
     *
     * @return mixed
     */
    public function getConfigParam($sName);

    /**
     * Stores config parameter value in config
     *
     * @param string $sName  config parameter name
     * @param string $sValue config parameter value
     */
    public function setConfigParam($sName, $sValue);

    /**
     * Updates or adds new shop configuration parameters to DB.
     * Arrays must be passed not serialized, serialized values are supported just for backward compatibility.
     *
     * @param string $sVarType Variable Type
     * @param string $sVarName Variable name
     * @param mixed  $sVarVal  Variable value (can be string, integer or array)
     * @param string $sShopId  Shop ID, default is current shop
     * @param string $sModule  Module name (empty for base options)
     */
    public function saveShopConfVar($sVarType, $sVarName, $sVarVal, $sShopId = null, $sModule = '');
}
