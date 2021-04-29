<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 * @version   OXID eShop EE
 */

namespace OxidProfessionalServices\EasyCredit\Core\Domain;

use OxidEsales\Eshop\Core\Base;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClient;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditConfigException;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;

/**
 * Returns and updates aquisition border values about easyCredit
 */
class EasyCreditAquisitionBorder extends Base {

    /** @var EasyCreditDic */
    private $dic = false;

    /**
     * Update aquisition value from easyCredit if needed by update period
     */
    public function updateAquisitionBorderIfNeeded() {

        if(!$this->mustUpdate() ) {
            return;
        }
        $this->updateAquisitionBorder();
    }

    /**
     * Force to update aquisition value from easyCredit
     */
    public function updateAquisitionBorder() {

        try {
            $response = $this->callWsAquisition();
            $this->saveAquisition((double)$response->restbetrag );
            return true;
        }
        catch (\Exception $ex) {
            $this->handleException($ex);
        }

        $this->saveAquisition(null); //set to invalid
        return false;
    }

    /**
     * Saves aquisition value and coresponding config data
     *
     * @param $aquisitionValue double
     */
    protected function saveAquisition($aquisitionValue)
    {
        $config = $this->getDic()->getConfig();
        $config->setConfigParam("oxpsECAquisitionBorderValue", $aquisitionValue);
        $config->saveShopConfVar('str', 'oxpsECAquisitionBorderValue', $aquisitionValue, $this->getShopId(), 'module:oxpseasycredit');

        $now = Registry::get("oxUtilsDate")->getTime();
        $now = date('Y-m-d H:i', $now);
        $config->setConfigParam("oxpsECAquisitionBorderLastUpdate", $now);
        $config->saveShopConfVar('str', 'oxpsECAquisitionBorderLastUpdate', $now, $this->getShopId(), 'module:oxpseasycredit');
    }

    /**
     * Calls aquisition webservice endpoint
     *
     * @var string $endpoint name of service
     * @var array $additionalArguments args which can be used in url
     * @var array $queryArguments query args
     * @var array $data postdata
     * @return string response of webservice
     * @throws \Exception if something happened
     */
    protected function callWsAquisition()
    {
        return $this->getWebServiceClient()->execute();
    }

    /**
     * Returns the dic container.
     *
     * @return EasyCreditDic
     * @throws SystemComponentException
     */
    protected function getDic()
    {
        if(!$this->dic) {
            $this->dic = EasyCreditDicFactory::getDic();
        }

        return $this->dic;
    }

    /**
     * Handles \Exception
     * @param $ex \Exception
     */
    protected function handleException($ex)
    {
        $oEx = oxNew('oxExceptionToDisplay');
        $oEx->setMessage($ex->getMessage());
        Registry::get("oxUtilsView")->addErrorToDisplay($oEx);
        $this->getDic()->getLogging()->log($ex->getMessage());
    }

    /**
     * Determine whether an update of aquisition value is needed
     * Update is needed if last update is outdated by period
     *
     * @return bool
     */
    protected function mustUpdate()
    {
        $updateIntervallInScounds = $this->getUpdateIntervallInSeconds();
        if( empty($updateIntervallInScounds) ) {
            return false;
        }

        $lastupdate = $this->getDic()->getConfig()->getConfigParam("oxpsECAquisitionBorderLastUpdate");
        if( $lastupdate && strtotime($lastupdate) !== FALSE) {
            //parse date to time

            if( (strtotime($lastupdate) + $updateIntervallInScounds) < time()) {
                return true;
            }
        }
        return true;
    }

    /**
     * Return update intervall in seconds or null, if nothing is defined
     *
     * @return int
     */
    protected function getUpdateIntervallInSeconds()
    {
        $updateIntervallInMinutes = $this->getDic()->getConfig()->getConfigParam("oxpsECAquBorderUpdateIntervalMin");
        if( empty($updateIntervallInMinutes) ) {
            return null;
        }

        //is update intervall syntactical valid?
        if(!is_numeric($updateIntervallInMinutes) || (int)$updateIntervallInMinutes != $updateIntervallInMinutes) {
            return null;
        }
        return (int)$updateIntervallInMinutes * 60;
    }

    /**
     * Returns aquisition value
     *
     * @return float
     */
    public function getCurrentAquisitionBorderValue()
    {
        $borderValue = $this->getDic()->getConfig()->getConfigParam("oxpsECAquisitionBorderValue");
        if( $borderValue && is_numeric($borderValue) ) {
            return (double)$borderValue;
        }
        return null;
    }

    /**
     * Returns if aquisition value should be considered by webshop frontend (reduced payment types)
     *
     * @return boolean
     * @throws SystemComponentException
     */
    public function considerInFrontend()
    {
        return $this->getDic()->getConfig()->getConfigParam("oxpsECAquBorderConsiderFrontend");
    }

    /**
     * Creates and Returns a web service client object.
     *
     * @return EasyCreditWebServiceClient
     * @throws SystemComponentException
     * @throws EasyCreditConfigException
     * @throws EasyCreditCurlException
     */
    protected function getWebServiceClient()
    {
        $webShopId = $this->getDic()->getApiConfig()->getWebShopId();
        return EasyCreditWebServiceClientFactory::getWebServiceClient(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_AQUISITION
            , $this->getDic()
            , array($webShopId)
            , array()
            , true);
    }

    protected function getShopId()
    {
        return $this->getDic()->getConfig()->getShopId();
    }
}