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

namespace OxidProfessionalServices\EasyCredit\Core\Api;

use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditConfigException;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;

/**
 * Class EasyCreditWebServiceClientFactory
 *
 * Builds a web service client capable for the specified rest function.
 */
class EasyCreditWebServiceClientFactory
{
    /**
     * @param string $serviceName
     * @param EasyCreditDic $dic
     * @param array|null $additionalArguments
     * @param array|null $queryArguments
     * @param bool $addheaders
     *
     * @return EasyCreditWebServiceClient
     * @throws SystemComponentException
     * @throws EasyCreditConfigException
     * @throws EasyCreditCurlException
     */
    public static function getWebServiceClient(
        $serviceName,
        EasyCreditDic $dic,
        array $additionalArguments = array(),
        array $queryArguments = array(),
        $addheaders = false
    ) {
        /** @var EasyCreditWebServiceClient $client */
        $client = oxNew(EasyCreditWebServiceClient::class);

        $apiConfig = $dic->getApiConfig();

        $client->setLogging($dic->getLogging());
        $client->setHttpmethod($apiConfig->getServiceHttpMethod($serviceName));
        $client->setBaseUrl($apiConfig->getBaseUrl($serviceName));
        $client->setFunction(
            $apiConfig->getServiceRestFunction($serviceName),
            $additionalArguments,
            array_merge($apiConfig->getServiceRestFunctionArguments($serviceName), $queryArguments)
        );

        $scheme = $apiConfig->getValidationScheme($serviceName);
        if ($scheme) {
            $client->setResponseValidator(
                oxNew(
                    EasyCreditResponseValidator::class,
                    $scheme
                )
            );
        }

        if( $addheaders ) {
            $headers = array(
                "Content-Type: application/json;charset=UTF-8",
                "tbk-rk-shop: " . $apiConfig->getWebshopId(),
                "tbk-rk-token: " . $apiConfig->getWebShopToken()
            );
            $client->setRequestHeaders($headers);
        }

        return $client;
    }
}