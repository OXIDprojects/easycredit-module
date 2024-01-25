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


use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidSolutionCatalysts\EasyCredit\Core\PayLoad\EasyCreditPayloadFactory;
use OxidSolutionCatalysts\EasyCredit\Service\EasyCreditModuleSettings;

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
            EasyCreditDic::class,
            oxNew(EasyCreditDicSession::class, Registry::getSession()),
            oxNew(EasyCreditApiConfig::class, self::getApiConfigArray()),
            oxNew(EasyCreditPayloadFactory::class),
            oxNew(EasyCreditLogging::class, self::getLoggingConfigArray()),
            oxNew(EasyCreditDicConfig::class, Registry::getConfig())
        );
    }

    public static function getApiConfigArray()
    {
        $config = ContainerFactory::getInstance()
            ->getContainer()
            ->get(EasyCreditModuleSettings::class);

        $services = self::getServices();
        $validationSchemes = self::getValidationSchemes();

        return array(
            EasyCreditApiConfig::API_CONFIG_CREDENTIALS => array(
                EasyCreditApiConfig::API_CONFIG_CREDENTIAL_BASE_URL      => $config->getOxpsECBaseUrl(),
                EasyCreditApiConfig::API_CONFIG_CREDENTIAL_APP_URL       => $config->getOxpsECDealerInterfaceUrl(),
                EasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_ID    => $config->getOxpsECWebshopId(),
                EasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_TOKEN => $config->getOxpsECWebshopToken(),
            ),
            EasyCreditApiConfig::API_CONFIG_SERVICES => $services,
            EasyCreditApiConfig::API_CONFIG_VALIDATION_SCHEMES => $validationSchemes
        );
    }

    private static function getLoggingConfigArray()
    {
        $config = Registry::getConfig();
        $moduleSettings = ContainerFactory::getInstance()
            ->getContainer()
            ->get(EasyCreditModuleSettings::class);

        return array(
            EasyCreditLogging::LOG_CONFIG_LOG_DIR     => $config->getLogsDir(),
            EasyCreditLogging::LOG_CONFIG_LOG_ENABLED => $moduleSettings->getOxpsECLogging(),
        );
    }

    private static function getJsonFromFile($filepath)
    {
        $json = file_get_contents($filepath);
        return json_decode($json,true);
    }

    private static function getServices()
    {
        return self::getJsonFromFile(__DIR__ . '/Config/services.json');
    }

    private static function getValidationSchemes()
    {
        return self::getJsonFromFile(__DIR__ . '/Config/validation.json');
    }
}
