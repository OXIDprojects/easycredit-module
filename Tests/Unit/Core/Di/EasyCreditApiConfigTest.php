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
 * Class oxpsEasyCreditApiConfigTest
 */
class oxpsEasyCreditApiConfigTest extends OxidTestCase
{
    const WEBSHOP_ID = '7';
    const WEBSHOP_TOKEN = 'A1378XY';

    /** @var oxpsEasyCreditApiConfig */
    private $apiConfig;

    /**
     * Set up test environment
     *
     * @return null
     * @throws oxSystemComponentException
     */
    public function setUp()
    {
        parent::setUp();

        $apiConfigArray = oxpsEasyCreditDicFactory::getApiConfigArray();

        $credentials = $apiConfigArray[oxpsEasyCreditApiConfig::API_CONFIG_CREDENTIALS];
        $credentials[oxpsEasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_ID] = self::WEBSHOP_ID;
        $credentials[oxpsEasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_TOKEN] = self::WEBSHOP_TOKEN;

        $apiConfigArray[oxpsEasyCreditApiConfig::API_CONFIG_CREDENTIALS] = $credentials;

        /** @var oxpsEasyCreditApiConfig $apiConfig */
        $this->apiConfig = oxNew('oxpsEasyCreditApiConfig', $apiConfigArray);
    }

    /**
     * Tear down test environment
     *
     * @return null
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetApiConfigValue()
    {
        $services = $this->apiConfig->getApiConfigValue(oxpsEasyCreditApiConfig::API_CONFIG_SERVICES);
        $this->assertNotNull($services);
        $this->assertTrue(is_array($services));

        $sampleService = $services[oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN];
        $this->assertNotNull($sampleService);
        $this->assertTrue(is_array($sampleService));
        $this->assertEquals('get', $sampleService['httpMethod']);
        $this->assertEquals('/v1/modellrechnung/guenstigsterRatenplan', $sampleService['restFunction']);
    }

    public function testGetServiceHttpMethodExisting()
    {
        $this->assertEquals('get', $this->apiConfig->getServiceHttpMethod(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE));
    }

    /**
     * @expectedException oxpsEasyCreditConfigException
     * @expectedExceptionMessage Service name 'non existing service' is not configured.
     */
    public function testGetServiceHttpMethodNonExisting()
    {
        $this->assertEquals('get', $this->apiConfig->getServiceHttpMethod('non existing service'));
    }

    public function testGetServiceRestFunction()
    {
        $this->assertEquals('/v1/texte/zustimmung/%s', $this->apiConfig->getServiceRestFunction(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE));
    }

    public function testGetServiceRestFunctionArguments()
    {
        $expected = array(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_WEBSHOP_ID => self::WEBSHOP_ID);
        $this->assertEquals($expected, $this->apiConfig->getServiceRestFunctionArguments(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE));
    }

    public function testGetBaseUrl()
    {
        $this->assertEquals('https://ratenkauf.easycredit.de/ratenkauf-ws/rest', $this->apiConfig->getBaseUrl());
    }

    public function testGetWebShopId()
    {
        $this->assertEquals(self::WEBSHOP_ID, $this->apiConfig->getWebShopId());
    }

    public function testGetWebShopToken()
    {
        $this->assertEquals(self::WEBSHOP_TOKEN, $this->apiConfig->getWebShopToken());
    }

    public function testGetValidationScheme()
    {
        $validationScheme = $this->apiConfig->getValidationScheme(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_VORGANG);

        $this->assertNotNull($validationScheme);
        $this->assertTrue(is_array($validationScheme));

        $validationSchemeValues = $validationScheme[0];
        $this->assertNotNull($validationSchemeValues);
        $this->assertTrue(is_array($validationSchemeValues));
        $this->assertEquals('tbVorgangskennung', $validationSchemeValues[oxpsEasyCreditResponseValidator::VALIDATION_KEY_FIELDNAME]);
        $this->assertEquals(1, $validationSchemeValues[oxpsEasyCreditResponseValidator::VALIDATION_KEY_REQUIRED]);
    }

    public function testGetRedirectUrl()
    {
        $this->assertEquals('https://ratenkauf.easycredit.de/ratenkauf/content/intern/einstieg.jsf?vorgangskennung=%s', $this->apiConfig->getRedirectUrl());
    }

    public function testGetEasyCreditInstalmentPaymentId()
    {
        $this->assertEquals('easycreditinstallment', $this->apiConfig->getEasyCreditInstalmentPaymentId());
    }

    public function testGetEasyCreditModuleId()
    {
        $this->assertEquals('oxpseasycredit', $this->apiConfig->getEasyCreditModuleId());
    }
}
