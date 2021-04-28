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

/**
 * Interface DicConfigInterface
 *
 * Providing a interface for http and unittest.
 */
interface oxpsEasyCreditDicConfigInterface
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