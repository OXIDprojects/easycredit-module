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
 * @copyright (C) OXID eSales AG 2003-2021
 */

namespace OxidSolutionCatalysts\EasyCredit\Core\Di;

use OxidSolutionCatalysts\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidSolutionCatalysts\EasyCredit\Core\PayLoad\EasyCreditPayloadFactory;

/**
 * Class Dic
 *
 * Providing session-, config-data and payload factory instance.
 */
class EasyCreditDic
{
    /** @var EasyCreditDicSession */
    private $dicSession;

    /** @var EasyCreditApiConfig */
    private $apiConfig;

    /** @var EasyCreditPayloadFactory */
    private $payloadFactory;

    /** @var EasyCreditLogging */
    private $logging;

    /** @var EasyCreditDicConfig */
    private $dicConfig;

    /**
     * Dic constructor.
     *
     * @param EasyCreditDicSession     $dicSession
     * @param EasyCreditApiConfig      $apiConfig
     * @param EasyCreditPayloadFactory $payloadFactory
     * @param EasyCreditLogging        $logging
     * @param EasyCreditDicConfig          $dicConfig
     */
    public function __construct($dicSession, $apiConfig, $payloadFactory, $logging, $dicConfig)
    {
        $this->dicSession = $dicSession;
        $this->apiConfig = $apiConfig;
        $this->payloadFactory = $payloadFactory;
        $this->logging = $logging;
        $this->dicConfig = $dicConfig;
    }

    /**
     * @return EasyCreditDicSession
     * @codeCoverageIgnore
     */
    public function getSession()
    {
        return $this->dicSession;
    }

    /**
     * @return EasyCreditApiConfig
     * @codeCoverageIgnore
     */
    public function getApiConfig()
    {
        return $this->apiConfig;
    }

    /**
     * @return EasyCreditPayloadFactory
     * @codeCoverageIgnore
     */
    public function getPayloadFactory()
    {
        return $this->payloadFactory;
    }

    /**
     * @return EasyCreditLogging
     * @codeCoverageIgnore
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * @return EasyCreditDicConfig
     * @codeCoverageIgnore
     */
    public function getConfig()
    {
        return $this->dicConfig;
    }


}
