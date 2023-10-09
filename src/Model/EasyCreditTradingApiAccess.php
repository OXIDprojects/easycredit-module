<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2021
 */

namespace OxidSolutionCatalysts\EasyCredit\Application\Model;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDic;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Exception\EasyCreditException;

/**
 * Class EasyCreditTradingApiAccess: Interfsace class to access easy credit trading api functionality.
 *
 * @package OxidSolutionCatalysts\EasyCredit\Application\Model
 */
class EasyCreditTradingApiAccess
{
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN            = 'LIEFERUNG_MELDEN';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN_AUSLAUFEND = 'LIEFERUNG_MELDEN_AUSLAUFEND';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_IN_ABRECHNUNG               = 'IN_ABRECHNUNG';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ABGERECHNET                 = 'ABGERECHNET';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_AUSLAUFEND                  = 'AUSLAUFEND';

    /**
     * @var Order|null
     */
    protected $order = null;

    /**
     * EasyCreditTradingApiAccess constructor. Set order object, if given
     *
     * @param null $order
     */
    public function __construct($order = null)
    {
        $this->order = is_null($order) ? oxNew(Order::class) : $order;
    }

    /**
     * Load order data from trading api. If flag is true state is updated in local storage.
     *
     * @param false $blUpdateLocalOrderState Shall it update local storage
     *
     * @return mixed
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditConfigException
     */
    public function getOrderData($blUpdateLocalOrderState = false)
    {
        $service  = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_STATE,
            EasyCreditDicFactory::getDic(),
            [$this->order->oxorder__ecredfunctionalid->value],
            [],
            true
        );
        $response = $service->execute();
        if ($blUpdateLocalOrderState) {
            $state                                    = $response->ergebnisse[0]->haendlerstatusV2;
            $this->order->oxorder__ecreddeliverystate = new Field($state, Field::T_RAW);
            $this->order->save();
        }

        return $response->ergebnisse;
    }

    /**
     * Load order state from trading api and translate haendlerstatusv2 value.
     *
     * @param false $blUpdateLocalOrderState
     *
     * @return array|string
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditConfigException
     */
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

    /**
     * Set haendlerstatusv2 value to delivered at ec trading api interface.
     *
     * @return \stdClass
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditConfigException
     */
    public function setOrderDeliveredState()
    {
        $service = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_REPORT,
            EasyCreditDicFactory::getDic(),
            [$this->order->oxorder__ecredfunctionalid->value],
            [],
            true
        );
        return $service->execute();
    }


    /**
     * Create service to access ec trading api.
     *
     * @param               $serviceName
     * @param EasyCreditDic $dic
     * @param array         $additionalArguments
     * @param array         $queryArguments
     * @param false         $addheaders
     *
     * @return \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditWebServiceClient
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditConfigException
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

    /**
     * Send reversal data for an order to ec trading api.
     *
     * @param $amount
     * @param $reason
     *
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditConfigException
     */
    public function sendReversal($amount, $reason)
    {
        $service  = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_ORDER_REVERSAL,
            EasyCreditDicFactory::getDic(),
            [$this->order->getFieldData('ecredfunctionalid')],
            [],
            true
        );
        $response = $service->execute([
                                          'datum'  => date('Y-m-d'),
                                          'grund'  => $reason,
                                          'betrag' => $amount,
                                      ]);
    }

    /**
     * Load orders by date from, to and state.
     * (ATM unused, but implemented for testing stuff)
     *
     * @param string $from  Start date to search
     * @param string $to    End date to search
     * @param string $state haendlerstatus to filter for
     *
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditConfigException
     */
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

        return $this->assignShopOrderData($response->ergebnisse);
    }

    /**
     * Ensure for each delivered data set fom ec api there is an order in shop storage with same functional id.
     * @param array $ecorderdata
     *
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     */
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