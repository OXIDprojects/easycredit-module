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

namespace OxidProfessionalServices\EasyCredit\Core\Di;


use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditPayment;

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
 * Class ApiConfig
 *
 * Providing the api config (i.e. module config) read-only.
 */
class EasyCreditApiConfig
{
    const API_CONFIG_EASYCREDIT_MODULE_ID = "oxpseasycredit";

    const API_CONFIG_CREDENTIALS = 'credentials';
    const API_CONFIG_CREDENTIAL_BASE_URL = 'oxpsEasyCreditWebshopBaseUrl';
    const API_CONFIG_CREDENTIAL_APP_URL = 'oxpsEasyCreditDealerInterfaceUrl';
    const API_CONFIG_CREDENTIAL_WEBSHOP_ID = 'oxpsECWebshopId';
    const API_CONFIG_CREDENTIAL_WEBSHOP_TOKEN = 'oxpsECWebshopToken';
    const API_CONFIG_LOG_ENABLED = 'oxpsEasyCreditLogEnabled';

    const API_CONFIG_SERVICES = 'services';
    const API_CONFIG_SERVICE_HTTP_METHOD = 'httpMethod';
    const API_CONFIG_SERVICE_REST_FUNCTION = 'restFunction';
    const API_CONFIG_SERVICE_ENDPOINT_TYPE = 'endpointtype';
    const API_CONFIG_SERVICE_ENDPOINT_TYPE_RATENKAUF = 'ratenkauf';
    const API_CONFIG_SERVICE_ENDPOINT_TYPE_DEALER_INTERFACE = 'haendlerinterface';

    const API_CONFIG_VALIDATION_SCHEMES = 'validationSchemes';

    const API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_DURCHFUEHREN = 'v1_modellrechnung_durchfuehren';
    const API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN = 'v1_modellrechnung_guenstigsterRatenplan';
    const API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE = 'v1_texte_zustimmung';
    const API_CONFIG_SERVICE_NAME_V1_VORGANG = 'v1_vorgang';
    const API_CONFIG_SERVICE_NAME_V1_DECISION = 'v1_decision';
    const API_CONFIG_SERVICE_NAME_V1_FINANCIAL_INFORMATION = 'v1_financialinformation';
    const API_CONFIG_SERVICE_NAME_V1_FINANZIERUNG = 'v1_finanzierung';
    const API_CONFIG_SERVICE_NAME_V1_BESTAETIGEN = 'v1_bestaetigen';
    const API_CONFIG_SERVICE_NAME_V1_AQUISITION = 'v1_aquisition';
    const API_CONFIG_SERVICE_NAME_V2_DELIVERY_REPORT = 'v2_delivery_report';
    const API_CONFIG_SERVICE_NAME_V2_DELIVERY_STATE = 'v2_delivery_state';
    const API_CONFIG_SERVICE_NAME_V2_ORDER_OVERVIEW = 'v2_transaktionen_suchen';
    const API_CONFIG_SERVICE_NAME_V2_ORDER_REVERSAL = 'v2_transaktionen_storno';

    const API_CONFIG_SERVICE_REST_ARGUMENT_WEBSHOP_ID = 'webshopId';

    const API_CONFIG_SERVICE_REST_ARGUMENTS = 'restArguments';
    const API_CONFIG_SERVICE_REST_ARGUMENT_FINANZIERUNGSBETRAG = 'finanzierungsbetrag';

    const API_REDIRECT_URL = "https://ratenkauf.easycredit.de/ratenkauf/content/intern/einstieg.jsf?vorgangskennung=%s";

    private $config;

    /**
     * ApiConfig constructor.
     *
     * @param array apiConfig
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Returns the config value stored under the given key or false if the key does not exist.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getApiConfigValue($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : false;
    }

    /**
     * Set the underlying config array.
     *
     * @param array $config
     * @codeCoverageIgnore
     */
    public function setApiConfig(array $config)
    {
        $this->config = $config;
    }

    protected function getCredentials()
    {
        return $this->getApiConfigValue(self::API_CONFIG_CREDENTIALS);
    }

    /**
     * @return mixed
     */
    protected function getServices()
    {
        return $this->getApiConfigValue(self::API_CONFIG_SERVICES);
    }

    /**
     * @param $serviceName
     *
     * @return mixed
     * @throws EasyCreditConfigException
     */
    protected function getService($serviceName)
    {
        $services = $this->getServices();
        if (isset($services[$serviceName])) {
            return $services[$serviceName];
        } else {
            throw new EasyCreditConfigException("Service name '$serviceName' is not configured.");
        }
    }

    /**
     * @param $serviceName
     *
     * @return mixed
     * @throws EasyCreditConfigException
     */
    public function getServiceHttpMethod($serviceName)
    {
        $service = $this->getService($serviceName);
        return $service[self::API_CONFIG_SERVICE_HTTP_METHOD];
    }

    /**
     * @param $serviceName
     *
     * @return mixed
     * @throws EasyCreditConfigException
     */
    public function getServiceRestFunction($serviceName)
    {
        $service = $this->getService($serviceName);
        return $service[self::API_CONFIG_SERVICE_REST_FUNCTION];
    }

    public function getServiceRestFunctionArguments($serviceName)
    {
        // TODO may be extend for other services?
        switch ($serviceName) {
            default:
                return array(self::API_CONFIG_SERVICE_REST_ARGUMENT_WEBSHOP_ID => $this->getWebShopId());
        }
    }

    public function getBaseUrl($serviceName = null)
    {
        $credentials = $this->getCredentials();
        $urlIdent = self::API_CONFIG_CREDENTIAL_BASE_URL;
        if ($serviceName) {
            $service = $this->getService($serviceName);
            if($service[self::API_CONFIG_SERVICE_ENDPOINT_TYPE] == self::API_CONFIG_SERVICE_ENDPOINT_TYPE_DEALER_INTERFACE) {
                $urlIdent = self::API_CONFIG_CREDENTIAL_APP_URL;
            }
        }
        return $credentials[$urlIdent];
    }

    public function getWebShopId()
    {
        $credentials = $this->getCredentials();
        return $credentials[self::API_CONFIG_CREDENTIAL_WEBSHOP_ID];
    }

    public function getWebShopToken()
    {
        $credentials = $this->getCredentials();
        return $credentials[self::API_CONFIG_CREDENTIAL_WEBSHOP_TOKEN];
    }

    protected function getValidationSchemes()
    {
        return $this->getApiConfigValue(self::API_CONFIG_VALIDATION_SCHEMES);
    }

    /**
     * @param $serviceName
     *
     * @return mixed
     */
    public function getValidationScheme($serviceName)
    {
        $schemes = $this->getValidationSchemes();
        return isset($schemes[$serviceName]) ? $schemes[$serviceName] : false;
    }

    public function getRedirectUrl()
    {
        return self::API_REDIRECT_URL;
    }

    public function getEasyCreditInstalmentPaymentId()
    {
        return EasyCreditPayment::EASYCREDIT_PAYMENTID;
    }

    public function getEasyCreditModuleId()
    {
        return self::API_CONFIG_EASYCREDIT_MODULE_ID;
    }
}