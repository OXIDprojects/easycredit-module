<?php


namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\AdminController;

class EasyCreditOverviewController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->_sThisTemplate = 'easycredit_overview.tpl';
    }
}