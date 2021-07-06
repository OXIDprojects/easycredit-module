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
use OxidEsales\Eshop\Core\Field;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;

/**
 * Class EasyCreditOrderOverviewController
 * Extends the order overviww controller with functionality used for easy credit payment orders.
 * Extend sendOrder method to set state at ec interface to delivered
 *
 * @package OxidProfessionalServices\EasyCredit\Application\Controller\Admin
 */
class EasyCreditOrderOverviewController extends EasyCreditOrderOverviewController_parent
{
    protected $order;

    /**
     * Set the state to delivered at easy credit dealer gateway.
     *
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException
     */
    public function sendOrder()
    {
        parent::sendOrder();
        $functionalId = $this->loadFunctionalIdFromOrder();
        if (!is_null($functionalId)) {
            $this->setOrderDelivered();
        }
    }

    /**
     * Load the EasyCredit order state from easy credit dealer gateway.
     *
     * @param string $functionalId The easy credit functional id for this order
     *
     * @return array|string
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException
     */
    public function getDeliveryState($order)
    {
        $tradingApiService = $this->getService($order);
        return $tradingApiService->getOrderState();
    }

    /**
     * @return string|null
     */
    protected function loadFunctionalIdFromOrder()
    {
        $this->loadOrder();

        return $this->order->oxorder__ecredfunctionalid->value;
    }

    /**
     * @return EasyCreditTradingApiAccess
     */
    protected function getService($order)
    {
        $tradingApiService = oxNew(EasyCreditTradingApiAccess::class, $order);
        return $tradingApiService;
    }

    /**
     * Load current order identified by edited object id
     */
    protected function loadOrder(): void
    {
        if (!$this->order) {
            $order = oxNew(Order::class);
            if ($order->load($this->getEditObjectId())) {
                $this->order = $order;
            }
        }
    }

    /**
     * Set order delivered state at easy credit interface and in order data.
     */
    protected function setOrderDelivered(): void
    {
        $tradingApiService = $this->getService($this->order);
        $tradingApiService->setOrderDeliveredState();

        $orderdata = $tradingApiService->getOrderData();
        $state = $orderdata[0]->haendlerstatusV2;

        $this->order->oxorder__ecreddeliverystate = new Field($state, Field::T_RAW);
        $this->order->save();
    }

}