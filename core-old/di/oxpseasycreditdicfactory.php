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
 * Class DicFactory
 *
 * Building a Dependency injection container with unmocked data, e.g. current oxSession and module config.
 */
class oxpsEasyCreditDicFactory
{
    /**
     * Creates and returns an unmocked Dic container.
     *
     * @return oxpsEasyCreditDic
     * @throws oxSystemComponentException
     */
    public static function getDic()
    {
        return oxNew(
            'oxpsEasyCreditDic',
            oxNew('oxpsEasyCreditDicSession', oxRegistry::getSession()),
            oxNew('oxpsEasyCreditApiConfig', self::getApiConfigArray()),
            oxNew('oxpsEasyCreditPayloadFactory'),
            oxNew('oxpsEasyCreditLogging', self::getLoggingConfigArray()),
            oxNew('oxpsEasyCreditDicConfig', oxRegistry::getConfig())
        );
    }

    public static function getApiConfigArray()
    {
        $config = oxRegistry::getConfig();

        $services = self::getServices();
        $validationSchemes = self::getValidationSchemes();

        return array(
            oxpsEasyCreditApiConfig::API_CONFIG_CREDENTIALS => array(
                oxpsEasyCreditApiConfig::API_CONFIG_CREDENTIAL_BASE_URL      => $config->getConfigParam('oxpsECBaseUrl'),
                oxpsEasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_ID    => $config->getConfigParam('oxpsECWebshopId'),
                oxpsEasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_TOKEN => $config->getConfigParam('oxpsECWebshopToken'),
            ),
            oxpsEasyCreditApiConfig::API_CONFIG_SERVICES => $services,
            oxpsEasyCreditApiConfig::API_CONFIG_VALIDATION_SCHEMES => $validationSchemes
        );
    }

    private static function getLoggingConfigArray()
    {
        $config = oxRegistry::getConfig();
        return array(
            oxpsEasyCreditLogging::LOG_CONFIG_LOG_DIR     => $config->getLogsDir(),
            oxpsEasyCreditLogging::LOG_CONFIG_LOG_ENABLED => $config->getConfigParam('oxpsECLogging'),
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
