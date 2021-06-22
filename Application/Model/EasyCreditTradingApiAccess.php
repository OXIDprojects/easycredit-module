<?php

namespace OxidProfessionalServices\EasyCredit\Application\Model;

use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;

class EasyCreditTradingApiAccess
{
    public function __construct($order)
    {
        $this->order = $order;
    }

    public function getOrderData()
    {
        $service = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_STATE,
            EasyCreditDicFactory::getDic(),
            [$this->order->oxorder__ecredfunctionalid->value],
            [],
            true,
        );
        $response = $service->execute();

        return $response->ergebnisse;
    }

    public function getOrderState()
    {
        $state = $this->getOrderData();
        if( count($state)) {
            $state = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_' . $state[0]->haendlerstatusV2);
        } else {
            $state = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ERROR');
        }

        return $state;
    }

    public function setOrderDeliveredState()
    {
        $service = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_REPORT,
            EasyCreditDicFactory::getDic(),
            [$this->order->oxorder__ecredfunctionalid->value],
            [],
            true,
        );
        $service->execute();
    }


    protected function getService(
        $serviceName,
        EasyCreditDic $dic,
        array $additionalArguments = array(),
        array $queryArguments = array(),
        $addheaders = false)
    {
        return EasyCreditWebServiceClientFactory::getWebServiceClient($serviceName, $dic, $additionalArguments, $queryArguments, $addheaders);
    }

}