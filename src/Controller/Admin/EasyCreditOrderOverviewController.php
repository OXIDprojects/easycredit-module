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

namespace OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidSolutionCatalysts\EasyCredit\Application\Model\EasyCreditTradingApiAccess;

/**
 * Class EasyCreditOrderOverviewController
 * Extends the order overviww controller with functionality used for easy credit payment orders.
 * Extend sendOrder method to set state at ec interface to delivered
 *
 * @package OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin
 */
class EasyCreditOrderOverviewController extends EasyCreditOrderOverviewController_parent
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * Set the state to delivered at easy credit trading gateway.
     *
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
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
     * Load the EasyCredit order state from easy credit trading gateway.
     *
     * @param string $functionalId The easy credit functional id for this order
     *
     * @return array|string
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
     */
    public function getDeliveryState($order)
    {
        $tradingApiService = $this->getService($order);
        return $tradingApiService->getOrderState();
    }

    /**
     * Load functional id from current order.
     *
     * @return string|null
     */
    protected function loadFunctionalIdFromOrder()
    {
        $this->loadOrder();

        return $this->order->oxorder__ecredfunctionalid->value;
    }

    /**
     * Get the ec trading api access service.
     *
     * @return EasyCreditTradingApiAccess
     */
    protected function getService($order)
    {
        return oxNew(EasyCreditTradingApiAccess::class, $order);
    }

    /**
     * Load current order identified by edited object id
     */
    protected function loadOrder()
    {
        if (!$this->order) {
            $order = oxNew(Order::class);
            if ($order->load($this->getEditObjectId())) {
                $this->order = $order;
            }
        }

        return $this->order;
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

        $order = $this->loadOrder();
        $order->oxorder__ecreddeliverystate = new Field($state, Field::T_RAW);
        $order->save();
    }

}