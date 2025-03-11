<?php

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Core\Api;

use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditCurlException;
use OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditHttpClient;
use OxidSolutionCatalysts\EasyCredit\Core\CrossCutting\EasyCreditLogging;

/**
 * Class EasyCreditHttpClientTest
 */
class EasyCreditHttpClientTest extends TestCase
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
        $client = $this->getMockBuilder(EasyCreditHttpClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['executeHttpRequest'])
            ->getMock();
        $client->expects($this->any())->method('executeHttpRequest')->willReturn('{"success": true}');

        $logging = oxNew(EasyCreditLogging::class, []);
        $client->setLogging($logging);

        $expected = new \stdClass();
        $expected->success = true;
        $this->assertEquals($expected, $client->executeJsonRequest('GET', 'https://test.url', new \stdClass()));
    }

    public function testExecuteHttpRequestWithoutHttpMethod()
    {
        $this->expectException(EasyCreditCurlException::class);
        $client = oxNew(EasyCreditHttpClient::class);
        $client->executeHttpRequest(null, null);
    }

    public function testExecuteHttpRequestWithWrongHttpMethod()
    {
        $this->expectException(EasyCreditCurlException::class);
        $client = oxNew(EasyCreditHttpClient::class);
        $client->executeHttpRequest('PUT', 'https://test.url'); // PUT is not supported by EasyCreditHttpClient
    }

    public function testExecuteHttpRequestWithoutServiceUrl()
    {
        $this->expectException(EasyCreditCurlException::class);
        $client = oxNew(EasyCreditHttpClient::class);
        $client->executeHttpRequest('GET', null);
    }

    public function testExecuteHttpRequestWithData()
    {
        $expected = '{"success": true}';

        $client = $this->getMockBuilder(EasyCreditHttpClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['curl_exec'])
            ->getMock();
        $client->expects($this->any())->method('curl_exec')->willReturn($expected);

        $this->assertEquals($expected, $client->executeHttpRequest('POST', 'https://test.url', []));
    }

}