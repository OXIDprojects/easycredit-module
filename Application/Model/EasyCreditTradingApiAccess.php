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
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidProfessionalServices\EasyCredit\Application\Model;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;

/**
 * Class EasyCreditTradingApiAccess: Class to access trading API
 *
 * @package OxidProfessionalServices\EasyCredit\Application\Model
 */
class EasyCreditTradingApiAccess
{
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN            = 'LIEFERUNG_MELDEN';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN_AUSLAUFEND = 'LIEFERUNG_MELDEN_AUSLAUFEND';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_IN_ABRECHNUNG               = 'IN_ABRECHNUNG';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ABGERECHNET                 = 'ABGERECHNET';
    const OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_AUSLAUFEND                  = 'AUSLAUFEND';

    /**
     * @var Order
     */
    protected $order = null;

    /**
     * EasyCreditTradingApiAccess constructor.
     *
     * @param Order $order
     */
    public function __construct($order = null)
    {
        $this->order = is_null($order) ? oxNew(Order::class) : $order;
    }

    /**
     * Get the easy credit data assigned to given order.
     *
     * @return array Order data at easy credit
     *
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditConfigException
     */
    public function getOrderData()
    {
        $service  = $this->getService(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_STATE,
            EasyCreditDicFactory::getDic(),
            [$this->order->oxorder__ecredfunctionalid->value],
            [],
            true,
        );
        $response = $service->execute();

        return $response->ergebnisse;
    }

    /**
     * Get the dealer state of order.
     *
     * @return string Easy credit order delivery state
     *
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditConfigException
     */
    public function getOrderState()
    {
        $state = $this->getOrderData();
        if (count($state)) {
            $state = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_' . $state[0]->haendlerstatusV2);
        } else {
            $state = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ERROR');
        }

        return $state;
    }

    /**
     * Set the state of order to delivered at easy credit interface.
     *
     * @return \stdClass
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditConfigException
     */
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
     * get trading API access class
     *
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

    /**
     * Load orders from tarding api filtered by given filter values
     *
     * @param string $from  Start date
     * @param string $to    End date
     * @param string $state State of order ar easy credit
     *
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditConfigException
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

        $result = $this->assignShopOrderData($response->ergebnisse);

        return $result;
    }

    /**
     * In case orders are provided by easy credit that do not have an order in eshop, this are filtered out.
     * This was case in test api access, where many more orders was exisitng than shop send to ec
     *
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