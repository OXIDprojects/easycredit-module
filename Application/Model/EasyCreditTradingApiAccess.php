<?php

namespace OxidProfessionalServices\EasyCredit\Application\Model;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;

class EasyCreditTradingApiAccess
{
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN            = 'LIEFERUNG_MELDEN';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN_AUSLAUFEND = 'LIEFERUNG_MELDEN_AUSLAUFEND';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_IN_ABRECHNUNG               = 'IN_ABRECHNUNG';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ABGERECHNET                 = 'ABGERECHNET';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_AUSLAUFEND                  = 'AUSLAUFEND';

    protected $order = null;

    public function __construct($order = null)
    {
        $this->order = is_null($order) ? oxNew(Order::class) : $order;
    }

    public function getOrderData($blUpdateLocalOrderState = false)
    {
        $service  = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_STATE,
            EasyCreditDicFactory::getDic(),
            [$this->order->oxorder__ecredfunctionalid->value],
            [],
            true,
        );
        $response = $service->execute();
        if( $blUpdateLocalOrderState) {
            $state = $response->ergebnisse[0]->haendlerstatusV2;
            $this->order->oxorder__ecreddeliverystate = new Field($state, Field::T_RAW);
            $this->order->save();
        }

        return $response->ergebnisse;
    }

    public function getOrderState($blUpdateLocalOrderState = false)
    {
        $state = $this->getOrderData($blUpdateLocalOrderState);
        if (count($state)) {
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
        return $service->execute();
    }


    /**
     * @param               $serviceName
     * @param EasyCreditDic $dic
     * @param array         $additionalArguments
     * @param array         $queryArguments
     * @param false         $addheaders
     *
     * @return \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClient
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditConfigException
     */
    protected function getService(
        $serviceName,
        EasyCreditDic $dic,
        array $additionalArguments = array(),
        array $queryArguments = array(),
        $addheaders = false
    ) {
        return EasyCreditWebServiceClientFactory::getWebServiceClient($serviceName, $dic, $additionalArguments,
                                                                      $queryArguments, $addheaders);
    }

    public function sendReversal($amount, $reason)
    {
        $service = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_ORDER_REVERSAL,
            EasyCreditDicFactory::getDic(),
            [$this->order->getFieldData('ecredfunctionalid')],
            [],
            true
        );
        $response = $service->execute([
                                          'datum' => date('Y-m-d'),
                                          'grund' => $reason,
                                          'betrag' => $amount,
                                      ]);
    }

    public function loadOrders($from, $to, $state)
    {
        $service  = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_ORDER_OVERVIEW,
            EasyCreditDicFactory::getDic(),
            [],
            ['von' => $from, 'bis' => $to, 'status' => $state],
            true
        );
        $response = $service->execute();

        $result = $this->assignShopOrderData($response->ergebnisse);

        return $result;
    }

    protected function assignShopOrderData(array $ecorderdata)
    {
        $results = [];
        foreach ($ecorderdata as $dataset) {
            $functionalId = $dataset->vorgangskennungFachlich;
            $order        = oxNew(Order::class);
            try {
                $order->loadByECFunctionalId($functionalId);
            } catch (EasyCreditException $e) {
                EasyCreditDicFactory::getDic()->getLogging()->log($e->getMessage());
                continue;
            }
            $dataset->oxorderid = $order->getId();
            $results[]          = $dataset;
        }

        return $results;
    }
}