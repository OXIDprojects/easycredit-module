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

namespace OxidSolutionCatalysts\EasyCredit\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidSolutionCatalysts\EasyCredit\Model\EasyCreditTradingApiAccess;

/**
 * Class EasyCreditOrderMainController
 * Extends the order overview controller with functionality used for easy credit payment orders.
 * Extend sendOrder method to set state at ec interface to delivered
 *
 * @package OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin
 */
class EasyCreditOrderMainController extends EasyCreditOrderMainController_parent
{
    /**
     * @var Order
     */
    protected $order;

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
     * Set the state to delivered at easy credit trading gateway.
     *
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
     */
    public function sendOrder()
    {
        parent::sendOrder();
        $order = $this->loadOrder();
        if (!empty($this->order->oxorder__ecredfunctionalid->value)) {
            $order->oscSetOrderDelivered();
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
        $tradingApiService = oxNew(EasyCreditTradingApiAccess::class, $order);
        return $tradingApiService->getOrderState();
    }
}
