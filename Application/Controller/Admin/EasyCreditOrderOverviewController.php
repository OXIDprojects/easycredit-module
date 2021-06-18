<?php


namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;

class EasyCreditOrderOverviewController extends EasyCreditOrderOverviewController_parent
{
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
            $this->callService(
                EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_REPORT,
                $functionalId,
            );
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
    public function getDeliveryState($functionalId)
    {
        return $this->callService(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_STATE, $functionalId);
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
        }

        return $functionalId;
    }

    /**
     * Call ec service identified by service name and parameter
     *
     * @param string $functionalId The easy credit functional id for this order
     *
     * @return array|string
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     * @throws \OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException
     */
    protected function callService($serviceName, $functionalId)
    {
        $service = $this->getService(
            $serviceName,
            EasyCreditDicFactory::getDic(),
            [$functionalId],
            [],
            true,
        );

        $response = $service->execute();
        $state    = $response->ergebnisse;
        if (count($state)) {
            $state = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_' . $state[0]->haendlerstatusV2);
        } else {
            $state = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ERROR');
        }
        return $state;
    }

    /**
     * Get correct ec service and set params.
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
}