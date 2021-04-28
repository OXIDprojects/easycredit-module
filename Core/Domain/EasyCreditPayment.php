<?php

namespace OxidProfessionalServices\EasyCredit\Core\Domain;

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

    /**
     * Return current saved aquisition value
     *
     * @return double
     */
    public function getEasyCreditAquisitionBorderValue()
    {
        /** @var $config oxConfig */
        $config = $this->getConfig();
        return $config->getConfigParam("oxpsECAquisitionBorderValue");
    }

    /**
     * Return user formatted saved aquisition value
     *
     * @return string
     */
    public function getFEasyCreditAquisitionBorderValue()
    {
        $aquisitionValue =  $this->getEasyCreditAquisitionBorderValue();
        if(!empty($aquisitionValue)) {
            $oConfig = oxRegistry::getConfig();
            $shopCurrency = $oConfig->getActShopCurrencyObject();
            return oxRegistry::getLang()->formatCurrency($aquisitionValue, $shopCurrency) . " " . $shopCurrency->name;
        }
        return null;
    }


    /**
     * Return user formatted last aquisition value update
     *
     * @return string
     */
    public function getFEasyCreditAquisitionBorderLastUpdate()
    {
        $lastupdate = $this->getEasyCreditAquisitionBorderLastUpdate();
        if( $lastupdate ) {
            return date("d.m.Y H:i", strtotime($lastupdate));
        }
        return null;
    }

    /**
     * Return last aquisition value update
     *
     * @return string
     */
    public function getEasyCreditAquisitionBorderLastUpdate()
    {
        /** @var $config oxConfig */
        $config = $this->getConfig();
        return $config->getConfigParam("oxpsECAquisitionBorderLastUpdate");
    }
}