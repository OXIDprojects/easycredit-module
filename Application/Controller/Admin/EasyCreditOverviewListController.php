<?php


namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;
use OxidEsales\Eshop\Core\Registry;

class EasyCreditOverviewListController extends AdminListController
{
    protected $_sThisTemplate = 'easycredit_overview_list.tpl';

    protected $filterParams = [];

    protected $states = [];

    public function init()
    {
        parent::init();
        $lang = Registry::getLang();

        $this->initializeFilterParams();
        $this->states = [
            'LIEFERUNG_MELDEN'            => $lang->translateString(' OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN', null, true),
            'LIEFERUNG_MELDEN_AUSLAUFEND' => $lang->translateString(' OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN_AUSLAUFEND', $lang->getEditLanguage(), true),
            'IN_BEARBEITUNG'              => $lang->translateString(' OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_IN_ABRECHNUNG', $lang->getEditLanguage(), true),
            'ABGERECHNET'                 => $lang->translateString(' OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ABGERECHNET', $lang->getEditLanguage(), true),
            'AUSLAUFEND'                  => $lang->translateString(' OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_AUSLAUFEND', $lang->getEditLanguage(), true),
        ];

    }

    public function render()
    {
        $this->addTplParam('filterparams', $this->filterParams);
        $this->addTplParam('states', $this->states);
        return $this->_sThisTemplate;
    }

    public function loadFilteredOrders()
    {
        $params             = Registry::getRequest()->getRequestEscapedParameter('ecfilter');
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

}