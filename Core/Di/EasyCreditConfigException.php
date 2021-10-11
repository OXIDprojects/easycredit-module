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
namespace OxidProfessionalServices\EasyCredit\Core\Di;


use OxidEsales\Eshop\Core\Exception\StandardException;

class EasyCreditConfigException extends StandardException
{
    /**
     * oxpsEasyCreditConfigException constructor.
     *
     * @param string $sMessage
     * @param int $iCode
     */
    public function __construct($sMessage = "not set", $iCode = 0)
    {
        parent::__construct($sMessage, $iCode);
    }
}