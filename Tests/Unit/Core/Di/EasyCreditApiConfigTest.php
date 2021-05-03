<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Core\Di;

use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditResponseValidator;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditConfigException;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;

/**
 * Class EasyCreditApiConfigTest
 */
class EasyCreditApiConfigTest extends UnitTestCase
{
    const WEBSHOP_ID = '7';
    const WEBSHOP_TOKEN = 'A1378XY';

    /** @var EasyCreditApiConfig */
    private $apiConfig;

    /**
     * Set up test environment
     *
     * @throws SystemComponentException
     */
    public function setUp(): void
    {
        parent::setUp();

        $apiConfigArray = EasyCreditDicFactory::getApiConfigArray();

        $credentials = $apiConfigArray[EasyCreditApiConfig::API_CONFIG_CREDENTIALS];
        $credentials[EasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_ID] = self::WEBSHOP_ID;
        $credentials[EasyCreditApiConfig::API_CONFIG_CREDENTIAL_WEBSHOP_TOKEN] = self::WEBSHOP_TOKEN;

        $apiConfigArray[EasyCreditApiConfig::API_CONFIG_CREDENTIALS] = $credentials;

        /** @var EasyCreditDicFactory $apiConfig */
        $this->apiConfig = oxNew(EasyCreditApiConfig::class, $apiConfigArray);
    }

    /**
     * Tear down test environment
     *
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetApiConfigValue()
    {
        $services = $this->apiConfig->getApiConfigValue(EasyCreditApiConfig::API_CONFIG_SERVICES);
        $this->assertNotNull($services);
        $this->assertIsArray($services);

        $sampleService = $services[EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN];
        $this->assertNotNull($sampleService);
        $this->assertIsArray($sampleService);
        $this->assertEquals('get', $sampleService['httpMethod']);
        $this->assertEquals('/v1/modellrechnung/guenstigsterRatenplan', $sampleService['restFunction']);
    }

    public function testGetServiceHttpMethodExisting()
    {
        $this->assertEquals('get', $this->apiConfig->getServiceHttpMethod(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE));
    }

    public function testGetServiceHttpMethodNonExisting()
    {
        $this->expectExceptionMessage("Service name 'non existing service' is not configured.");
        $this->expectException(EasyCreditConfigException::class);
        $this->assertEquals('get', $this->apiConfig->getServiceHttpMethod('non existing service'));
    }

    public function testGetServiceRestFunction()
    {
        $this->assertEquals('/v1/texte/zustimmung/%s', $this->apiConfig->getServiceRestFunction(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE));
    }

    public function testGetServiceRestFunctionArguments()
    {
        $expected = [EasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_WEBSHOP_ID => self::WEBSHOP_ID];
        $this->assertEquals($expected, $this->apiConfig->getServiceRestFunctionArguments(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_ZUSTIMMUNGSTEXTE));
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
        $validationScheme = $this->apiConfig->getValidationScheme(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_VORGANG);

        $this->assertNotNull($validationScheme);
        $this->assertIsArray($validationScheme);

        $validationSchemeValues = $validationScheme[0];
        $this->assertNotNull($validationSchemeValues);
        $this->assertIsArray($validationSchemeValues);
        $this->assertEquals('tbVorgangskennung', $validationSchemeValues[EasyCreditResponseValidator::VALIDATION_KEY_FIELDNAME]);
        $this->assertEquals(1, $validationSchemeValues[EasyCreditResponseValidator::VALIDATION_KEY_REQUIRED]);
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
