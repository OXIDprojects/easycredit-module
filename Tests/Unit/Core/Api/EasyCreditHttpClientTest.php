<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Core\Api;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditHttpClient;

/**
 * Class oxpsEasyCreditHttpClientTest
 */
class EasyCreditHttpClientTest extends UnitTestCase
{
    /**
     * Set up test environment
     *
     */
    public function setUp():void
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     */
    public function tearDown():void
    {
        parent::tearDown();
    }

    public function testExecuteJsonRequestWithoutHttpMethod()
    {
        $this->expectException(EasyCreditCurlException::class);
        $client = oxNew(EasyCreditHttpClient::class);
        $client->executeJsonRequest(null, null);
    }

    public function testExecuteJsonRequestWithoutServiceUrl()
    {
        $this->expectException(EasyCreditCurlException::class);
        $client = oxNew(EasyCreditHttpClient::class);
        $client->executeJsonRequest('GET', null);
    }

    public function testExecuteJsonRequestWithData()
    {
        $client = $this->getMock(EasyCreditHttpClient::class, ['executeHttpRequest']);
        $client->expects($this->any())->method('executeHttpRequest')->willReturn('{"success": true}');

        $logging = oxNew('EasyCreditLogging', []);
        $client->setLogging($logging);

        $expected = new \stdClass();
        $expected->success = true;
        $this->assertEquals($expected, $client->executeJsonRequest('GET', 'https://test.url', new \stdClass()));
    }

    public function testExecuteHttpRequestWithoutHttpMethod()
    {
        $this->expectException(oxpsEasyCreditCurlException::class);
        $client = oxNew(EasyCreditHttpClient::class);
        $client->executeHttpRequest(null, null);
    }

    public function testExecuteHttpRequestWithWrongHttpMethod()
    {
        $this->expectException(oxpsEasyCreditCurlException::class);
        $client = oxNew(EasyCreditHttpClient::class);
        $client->executeHttpRequest('PUT', 'https://test.url'); // PUT is not supported by EasyCreditHttpClient
    }

    public function testExecuteHttpRequestWithoutServiceUrl()
    {
        $this->expectException(oxpsEasyCreditCurlException::class);
        $client = oxNew(EasyCreditHttpClient::class);
        $client->executeHttpRequest('GET', null);
    }

    public function testExecuteHttpRequestWithData()
    {
        $expected = '{"success": true}';

        $client = $this->getMock(EasyCreditHttpClient::class, ['curl_exec']);
        $client->expects($this->any())->method('curl_exec')->willReturn($expected);

        $this->assertEquals($expected, $client->executeHttpRequest('POST', 'https://test.url', new \stdClass()));
    }

}