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
 */

namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditPayment;

/**
 * Order admin class for easyCredit
 *
 * Implements register "ratenkauf by easyCredit" in admin | order management | orders
 */
class EasyCreditOrderEasyCreditController extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
{
    /**
     * @var Order order
     */
    private $order = false;

    /**
     * Render method
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->_aViewData["sOxid"] = $this->getEditObjectId();

        if ($this->hasEasyCreditPayment()) {
            $this->_aViewData['order'] = $this->getOrder();
            $this->_aViewData['currency'] = $this->getOrder()->getOrderCurrency()->name;
            $this->_aViewData['confirmationresponse'] = $this->getEasyCreditConfirmationResponse();
        }

        return "oxpseasycredit_order_easycredit.tpl";
    }

    /**
     * Returns true if orders was payed with ratenkauf by easyCredit
     *
     * @return bool
     */
    public function hasEasyCreditPayment()
    {
        /** @var $order Order */
        $order = $this->getOrder();
        return $order && EasyCreditPayment::isEasyCreditInstallmentById($order->getFieldData('oxpaymenttype'));
    }

    /**
     * Returns current edited order object
     *
     * @return Order
     */
    protected function getOrder()
    {
        $soxId = $this->getEditObjectId();
        if ($this->order === false && isset($soxId) && $soxId != '-1') {
            $this->order = oxNew('oxOrder');
            $this->order->load($soxId);
        }

        return $this->order;
    }

    /**
     * Returns acknowlegement of easycredit of order corresponded payment process
     * Response is pretty painted for user
     *
     * @return string
     */
    protected function getEasyCreditConfirmationResponse()
    {
        $response = $this->getOrder()->oxorder__ecredconfirmresponse->value;
        if( $response ) {
            $response = unserialize(base64_decode($response));
            if( is_object($response)) {
                $response = json_encode($response, JSON_PRETTY_PRINT);
            }
        }
        return $response;
    }
}