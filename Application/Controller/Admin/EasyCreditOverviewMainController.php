<?php


namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;

class EasyCreditOverviewMainController extends AdminDetailsController
{
    protected $_sThisTemplate = 'easycredit_overview_main.tpl';

    public function render()
    {
        return $this->_sThisTemplate;
    }
}