<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       easycredit
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

/**
 * Class Dic
 *
 * Providing session-, config-data and payload factory instance.
 */
class oxpsEasyCreditDic
{
    /** @var oxpsEasyCreditDicSession */
    private $dicSession;

    /** @var oxpsEasyCreditApiConfig */
    private $apiConfig;

    /** @var oxpsEasyCreditPayloadFactory */
    private $payloadFactory;

    /** @var oxpsEasyCreditLogging */
    private $logging;

    /** @var oxpsEasyCreditDicConfig */
    private $dicConfig;

    /**
     * Dic constructor.
     *
     * @param oxpsEasyCreditDicSession $dicSession
     * @param oxpsEasyCreditApiConfig $apiConfig
     * @param oxpsEasyCreditPayloadFactory $payloadFactory
     * @param oxpsEasyCreditLogging $logging
     * @param oxpsEasyCreditDicConfig $dicConfig
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
     * @return oxpsEasyCreditDicSession
     * @codeCoverageIgnore
     */
    public function getSession()
    {
        return $this->dicSession;
    }

    /**
     * @return oxpsEasyCreditApiConfig
     * @codeCoverageIgnore
     */
    public function getApiConfig()
    {
        return $this->apiConfig;
    }

    /**
     * @return oxpsEasyCreditPayloadFactory
     * @codeCoverageIgnore
     */
    public function getPayloadFactory()
    {
        return $this->payloadFactory;
    }

    /**
     * @return oxpsEasyCreditLogging
     * @codeCoverageIgnore
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * @return oxpsEasyCreditDicConfig
     * @codeCoverageIgnore
     */
    public function getConfig()
    {
        return $this->dicConfig;
    }


}
