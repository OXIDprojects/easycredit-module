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
 * @copyright (C) OXID eSales AG 2003-2018
 * @version   OXID eShop EE
 */

/**
 * Order manager. Arranges user ordering data, checks/validates
 * it, on success stores ordering data to DB.
 */
class oxpsEasyCreditOrder extends oxpsEasyCreditOrder_parent
{

    /** @var oxpsEasyCreditDic */
    private $dic = false;

    /**
     * Returns current order payment object
     *
     * @return oxPayment
     */
    public function getPayment()
    {
        if ($this->_oPayment === null) {

            /** @var $payment oxPayment */
            $payment = $this->parentGetPayment();
            if ($payment && $payment->getId() == $this->getDic()->getApiConfig()->getEasyCreditInstalmentPaymentId()) {

                $this->checkStorage();
                $this->appendInstalmentRatesToPaymentDescription($payment);
            }
            else {
                $this->_oPayment = $payment;
            }
        }
        return $this->_oPayment;
    }

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
            /** @var $viewConfig oxViewConfig */
            $viewConfig = $this->getViewConfig();
            $logoFile = $viewConfig->getModulePath('oxpseasycredit', "out" . DIRECTORY_SEPARATOR . "pictures" . DIRECTORY_SEPARATOR . "eclogo.png");
            if( file_exists($logoFile)) {
                return $viewConfig->getModuleUrl('oxpseasycredit') . 'out/pictures/eclogo.png';
            }
        }
        catch (Exception $ex) {
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
        if( $storage ) {
            return $storage->getTilgungsplanTxt();
        }
        return null;
    }

    /**
     * Returns allgemeineVorgangsdaten
     *
     * @return null|stdClass
     */
    protected function getAllgemeineVorgangsdaten()
    {
        $storage = $this->getDicSession()->getStorage();
        if( $storage ) {
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
        if( $allgemeineVorgangsdaten ) {
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
        if( $storage ) {
            return $storage->getRatenplanTxt();
        }
        return null;
    }

    /**
     * Modify information about easycredit payment (append payment logo and individual installment rates)
     * Result: more info for customer
     *
     * @param $payment oxPayment
     */
    protected function appendInstalmentRatesToPaymentDescription($payment)
    {
        $paymentPlanTxt = $this->getPaymentPlanTxt();
        if ($paymentPlanTxt) {
            $paymentDescription = $payment->oxpayments__oxdesc->value;

            $logoUrl = $this->getEasyCreditLogoUrl();
            if ($logoUrl) {
                $logoUrlImgPattern = oxRegistry::getLang()->translateString("OXPS_EASY_CREDIT_LOGO_IMG");
                $paymentDescription = sprintf($logoUrlImgPattern, $logoUrl);
            }
            $paymentDescription .= "<p>" . $paymentPlanTxt . "</p>";
            $payment->oxpayments__oxdesc->value = new oxField($paymentDescription, oxField::T_RAW);
        }
    }

    /**
     * @return oxpsEasyCreditDicSession
     * @throws oxSystemComponentException
     */
    protected function getDicSession()
    {
        return $this->getDic()->getSession();
    }

    /**
     * Returns the dic container.
     *
     * @return oxpsEasyCreditDic
     * @throws oxSystemComponentException
     */
    protected function getDic()
    {
        if(!$this->dic) {
            $this->dic = oxpsEasyCreditDicFactory::getDic();
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
        $oEx = oxNew('oxExceptionToDisplay');
        $oEx->setMessage($i18nMessage);
        oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
    }

    /**
     * Checks current instalment storage. Redirects to payment page if there is not storage info.
     */
    protected function checkStorage()
    {
        $storage = $this->getDicSession()->getStorage();
        if (empty($storage)) {
            $this->handleUserException("OXPS_EASY_CREDIT_ERROR_EXPIRED");
            oxRegistry::getUtils()->redirect($this->getConfig()->getShopCurrentURL() . '&cl=payment', true, 302);
        }
    }
}
