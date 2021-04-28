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

namespace OxidProfessionalServices\EasyCredit\Core\Api;

/**
 * Class EasyCreditWebServiceClientFactory
 *
 * Builds a web service client capable for the specified rest function.
 */
class EasyCreditWebServiceClientFactory
{
    /**
     * @param string $serviceName
     * @param oxpsEasyCreditDic $dic
     * @param array|null $additionalArguments
     * @param array|null $queryArguments
     * @param bool $addheaders
     *
     * @return oxpsEasyCreditWebServiceClient
     * @throws oxSystemComponentException
     * @throws oxpsEasyCreditConfigException
     * @throws oxpsEasyCreditCurlException
     */
    public static function getWebServiceClient(
        $serviceName,
        oxpsEasyCreditDic $dic,
        array $additionalArguments = array(),
        array $queryArguments = array(),
        $addheaders = false
    ) {
        /** @var oxpsEasyCreditWebServiceClient $client */
        $client = oxNew('EasyCreditWebServiceClient');

        $apiConfig = $dic->getApiConfig();

        $client->setLogging($dic->getLogging());
        $client->setHttpmethod($apiConfig->getServiceHttpMethod($serviceName));
        $client->setBaseUrl($apiConfig->getBaseUrl());
        $client->setFunction(
            $apiConfig->getServiceRestFunction($serviceName),
            $additionalArguments,
            array_merge($apiConfig->getServiceRestFunctionArguments($serviceName), $queryArguments)
        );

        $scheme = $apiConfig->getValidationScheme($serviceName);
        if ($scheme) {
            $client->setResponseValidator(
                oxNew(
                    'EasyCreditResponseValidator',
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