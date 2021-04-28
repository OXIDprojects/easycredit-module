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

namespace OxidProfessionalServices\EasyCredit\Core\Di;


use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidProfessionalServices\EasyCredit\Core\PayLoad\EasyCreditPayloadFactory;

/**
 * Class DicFactory
 *
 * Building a Dependency injection container with unmocked data, e.g. current oxSession and module config.
 */
class EasyCreditDicFactory
{
    /**
     * Creates and returns an unmocked Dic container.
     *
     * @return EasyCreditDic
     * @throws SystemComponentException
     */
    public static function getDic()
    {
        return oxNew(
            'EasyCreditDic',
            oxNew(EasyCreditDicSession::class, Registry::getSession()),
            oxNew(EasyCreditApiConfig::class, self::getApiConfigArray()),
            oxNew(EasyCreditPayloadFactory::class),
            oxNew(EasyCreditLogging::class, self::getLoggingConfigArray()),
            oxNew(EasyCreditDicConfig::class, Registry::getConfig())
        );
    }

    public static function getApiConfigArray()
    {
        $config = Registry::getConfig();

        $services = self::getServices();
        $validationSchemes = self::getValidationSchemes();

        return array(
            EasyCreditApiConfig::API_CONFIG_CREDENTIALS => array(
                EasyCreditApiConfig::API_CONFIG_CREDENTIAL_BASE_URL      => $config->getConfigParam('oxpsECBaseUrl'),
                EasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_ID    => $config->getConfigParam('oxpsECWebshopId'),
                EasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_TOKEN => $config->getConfigParam('oxpsECWebshopToken'),
            ),
            EasyCreditApiConfig::API_CONFIG_SERVICES => $services,
            EasyCreditApiConfig::API_CONFIG_VALIDATION_SCHEMES => $validationSchemes
        );
    }

    private static function getLoggingConfigArray()
    {
        $config = Registry::getConfig();
        return array(
            EasyCreditLogging::LOG_CONFIG_LOG_DIR     => $config->getLogsDir(),
            EasyCreditLogging::LOG_CONFIG_LOG_ENABLED => $config->getConfigParam('oxpsECLogging'),
        );
    }

    private static function getJsonFromFile($filepath)
    {
        $json = file_get_contents($filepath);
        return json_decode($json,true);
    }

    private static function getServices()
    {
        return self::getJsonFromFile(__DIR__ . '/config/services.json');
    }

    private static function getValidationSchemes()
    {
        return self::getJsonFromFile(__DIR__ . '/config/validation.json');
    }
}
