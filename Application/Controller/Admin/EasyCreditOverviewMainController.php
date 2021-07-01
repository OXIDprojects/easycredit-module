<?php


namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;

class EasyCreditOverviewMainController extends AdminDetailsController
{
    protected $_sThisTemplate = 'easycredit_overview_main.tpl';

    protected $order = null;

    protected $user = null;

    public function render()
    {
        parent::render();

        $oxId = $this->_aViewData["oxid"] = $this->getEditObjectId();

        if (!empty($oxId) && $oxId != "-1") {
            try {
                $this->addTplParam('orderdata', $this->loadOrderData($oxId));
            } catch (EasyCreditException $e) {
                EasyCreditDicFactory::getDic()->getLogging()->log($e->getMessage());
            }
        }

        return $this->_sThisTemplate;
    }

    /**
     * @param string $oxId
     *
     * @throws EasyCreditException
     */
    protected function loadOrderData($oxId)
    {
        $this->loadOrder($oxId);

        $functionalId = $this->order->getFieldData('ecredfunctionalid');
        if (empty($functionalId)) {
            throw new EasyCreditException("No EasyCredit order with order id $oxId");
        }

        $model       = oxNew(EasyCreditTradingApiAccess::class, $this->order);
        $ecorderdata = $model->getOrderData();

        return [
            'oxorder' => $this->order,
            'ecorder' => $ecorderdata[0],
        ];
    }

    /**
     * @param string $oxId
     *
     * @return mixed|Order
     * @throws EasyCreditException
     */
    protected function loadOrder(string $oxId)
    {
        if (!$this->order) {
            $order = oxNew(Order::class);
            if (false === $order->load($oxId)) {
                throw new EasyCreditException("Can not load order with ID $oxId.");
            }
            $this->order = $order;
        }
    }
}