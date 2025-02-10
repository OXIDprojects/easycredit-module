<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Application\Controller;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Exception\ExceptionToDisplay;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ViewConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicSession;

/**
 * Order manager. Arranges user ordering data, checks/validates
 * it, on success stores ordering data to DB.
 */
class EasyCreditOrderController extends EasyCreditOrderController_parent
{
    /** @var EasyCreditDic */
    private $dic = false;

    /**
     * Returns current order payment object
     *
     * @return Payment
     */
    public function getPayment()
    {
        if ($this->_oPayment === null) {

            /** @var $payment Payment */
            $payment = $this->parentGetPayment();
            if ($payment && $payment->getId() == $this->getDic()->getApiConfig()->getEasyCreditInstalmentPaymentId()) {

                $this->checkStorage();
                $this->appendInstalmentRatesToPaymentDescription($payment);
            } else {
                $this->_oPayment = $payment;
            }
        }
        return $this->_oPayment;
    }

    /**
     * Call get payment at parent
     *
     * @return false|object|null
     */
    protected function parentGetPayment()
    {
        return parent::getPayment();
    }

    /**
     * Returns url to easycreditlogo. Will be viewed in fronted review page. It's more beautifull.
     *
     * @return string
     */
    protected function getEasyCreditLogoUrl()
    {
        try {
            /** @var $viewConfig ViewConfig */
            $viewConfig = $this->getViewConfig();
            $logoFile = $viewConfig->getModulePath('oxpseasycredit', "out" . DIRECTORY_SEPARATOR . "pictures" . DIRECTORY_SEPARATOR . "eclogo.png");
            if (file_exists($logoFile)) {
                return $viewConfig->getModuleUrl('oxpseasycredit') . 'out/pictures/eclogo.png';
            }
        } catch (\Exception $ex) {
            //that's expected, do nothing else
        }
        return null;
    }

    /**
     * Returns tilgungsplan for user display
     *
     * @return string
     */
    public function getTilgungsplanText()
    {
        $storage = $this->getDicSession()->getStorage();
        if ($storage) {
            return $storage->getTilgungsplanTxt();
        }
        return null;
    }

    /**
     * Returns allgemeineVorgangsdaten
     *
     * @return null|\stdClass
     */
    protected function getAllgemeineVorgangsdaten()
    {
        $storage = $this->getDicSession()->getStorage();
        if ($storage) {
            return $storage->getAllgemeineVorgangsdaten();
        }
        return null;
    }

    /**
     * Returns url to vorvertraglicheInformationen
     *
     * @return string
     */
    public function getUrlVorvertraglicheInformationen()
    {
        $allgemeineVorgangsdaten = $this->getAllgemeineVorgangsdaten();
        if ($allgemeineVorgangsdaten) {
            return $allgemeineVorgangsdaten->urlVorvertraglicheInformationen;
        }
        return null;
    }

    /**
     * Returns formatted payment plan for user display
     *
     * @return string
     */
    public function getPaymentPlanTxt()
    {
        $storage = $this->getDicSession()->getStorage();
        if ($storage) {
            return $storage->getRatenplanTxt();
        }
        return null;
    }

    /**
     * Modify information about easycredit payment (append payment logo and individual installment rates)
     * Result: more info for customer
     *
     * @param $payment Payment
     */
    protected function appendInstalmentRatesToPaymentDescription($payment)
    {
        $paymentPlanTxt = $this->getPaymentPlanTxt();
        if ($paymentPlanTxt) {
            $paymentDescription = $payment->oxpayments__oxdesc->value;

            $logoUrl = $this->getEasyCreditLogoUrl();
            if ($logoUrl) {
                $logoUrlImgPattern = Registry::getLang()->translateString("OXPS_EASY_CREDIT_LOGO_IMG");
                $paymentDescription = sprintf($logoUrlImgPattern, $logoUrl);
            }
            $paymentDescription .= "<p>" . $paymentPlanTxt . "</p>";
            $payment->oxpayments__oxdesc->value = new Field($paymentDescription, Field::T_RAW);
        }
    }

    /**
     * @return EasyCreditDicSession
     * @throws SystemComponentException
     */
    protected function getDicSession()
    {
        return $this->getDic()->getSession();
    }

    /**
     * Returns the dic container.
     *
     * @return EasyCreditDic
     * @throws SystemComponentException^
     */
    protected function getDic()
    {
        if (!$this->dic) {
            $this->dic = EasyCreditDicFactory::getDic();
        }

        return $this->dic;
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
     * Checks current instalment storage. Redirects to payment page if there is not storage info.
     */
    protected function checkStorage()
    {
        $storage = $this->getDicSession()->getStorage();
        if (empty($storage)) {
            $this->handleUserException("OXPS_EASY_CREDIT_ERROR_EXPIRED");
            Registry::getUtils()->redirect(Registry::getConfig()->getShopCurrentURL() . '&cl=payment', true, 302);
        }
    }
}
