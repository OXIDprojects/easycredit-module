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

namespace OxidProfessionalServices\EasyCredit\Core\Domain;

use OxidEsales\Eshop\Core\Registry;

/**
 * Class oxpsEasyCreditOxPayment
 */
class EasyCreditPayment extends EasyCreditPayment_parent
{
    /** string paymentid */
    const EASYCREDIT_PAYMENTID = "easycreditinstallment";

    /**
     * Returns true if payment is ratenkauf by easyCredit
     *
     * @return bool
     */
    public function isEasyCreditInstallment()
    {
        return self::isEasyCreditInstallmentById($this->getId());
    }

    /**
     * Returns true if payment is ratenkauf by easyCredit
     *
     * @param $paymentId string
     *
     * @return bool
     */
    public static function isEasyCreditInstallmentById($paymentId)
    {
        return $paymentId == self::EASYCREDIT_PAYMENTID;
    }
}