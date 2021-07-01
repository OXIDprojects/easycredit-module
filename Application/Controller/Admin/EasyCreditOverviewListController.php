<?php


namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;

class EasyCreditOverviewListController extends AdminListController
{
    protected $_sThisTemplate = 'easycredit_overview_list.tpl';

    protected $filterParams = [];

    protected $states = [];

    protected $editoxid = null;

    public function init()
    {
        parent::init();
        $lang = Registry::getLang();

        $this->initializeFilterParams();
        $this->states = [
            'LIEFERUNG_MELDEN'            => 'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN',
            'LIEFERUNG_MELDEN_AUSLAUFEND' => 'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN_AUSLAUFEND',
            'IN_ABRECHNUNG'               => 'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_IN_ABRECHNUNG',
            'ABGERECHNET'                 => 'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ABGERECHNET',
            'AUSLAUFEND'                  => 'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_AUSLAUFEND',
        ];

    }

    public function render()
    {
        parent::render();
        $this->addTplParam('filterparams', $this->filterParams);
        $this->addTplParam('states', $this->states);
        $this->addTplParam('ecorders', $this->loadEcOrders());
        $this->addTplParam('oxid', $this->editoxid);
        return $this->_sThisTemplate;
    }

    public function setFilter()
    {
        $params             = Registry::getRequest()->getRequestEscapedParameter('ecfilter');
        $this->editoxid     = Registry::getRequest()->getRequestEscapedParameter('oxid');
        $this->filterParams = [
            'start' => $params['date_from'],
            'end'   => $params['date_to'],
            'state' => $params['ec_state'],
        ];
    }

    protected function initializeFilterParams()
    {
        $endDate   = new \DateTime();
        $end       = $endDate->format('Y-m-d');
        $startDate = new \DateTime('-1 week');
        $start     = $startDate->format('Y-m-d');

        $state = '';

        $this->filterParams = [
            'start' => $start,
            'end'   => $end,
            'state' => $state,
        ];
    }

    protected function loadEcOrders()
    {
        $model  = oxNew(EasyCreditTradingApiAccess::class, oxNew(Order::class));
        $orders = $model->loadOrders(
            $this->filterParams['start'],
            $this->filterParams['end'],
            $this->filterParams['state']
        );

        return $orders;
    }
}