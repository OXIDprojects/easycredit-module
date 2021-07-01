<?php


namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Model\Order;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;

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
            $tradingApiService = $this->getService($this->order);
            $tradingApiService->setOrderDeliveredState();
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
        $functionalId = null;

        $order = oxNew(Order::class);
        if ($order->load($this->getEditObjectId())) {
            $functionalId = $order->oxorder__ecredfunctionalid->value;
            $this->order = $order;
        }

        return $functionalId;
    }

    /**
     * @return mixed|EasyCreditTradingApiAccess
     */
    protected function getService($order)
    {
        $tradingApiService = oxNew(EasyCreditTradingApiAccess::class, $order);
        return $tradingApiService;
    }

}