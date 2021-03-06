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
 * Class oxpsEasyCreditHttpClientTest
 */
class oxpsEasyCreditHttpClientTest extends OxidTestCase
{
    /**
     * Set up test environment
     *
     * @return null
     */
    public function setUp()
    {
        parent::setUp();
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

    /**
     * @expectedException oxpsEasyCreditCurlException
     */
    public function testExecuteJsonRequestWithoutHttpMethod()
    {
        $client = oxNew('oxpsEasyCreditHttpClient');
        $client->executeJsonRequest(null, null);
    }

    /**
     * @expectedException oxpsEasyCreditCurlException
     */
    public function testExecuteJsonRequestWithoutServiceUrl()
    {
        $client = oxNew('oxpsEasyCreditHttpClient');
        $client->executeJsonRequest('GET', null);
    }

    public function testExecuteJsonRequestWithData()
    {
        $client = $this->getMock('oxpsEasyCreditHttpClient', array('executeHttpRequest'));
        $client->expects($this->any())->method('executeHttpRequest')->willReturn('{"success": true}');

        $logging = oxNew('oxpsEasyCreditLogging', array());
        $client->setLogging($logging);

        $expected = new stdClass();
        $expected->success = true;
        $this->assertEquals($expected, $client->executeJsonRequest('GET', 'https://test.url', new stdClass()));
    }

    /**
     * @expectedException oxpsEasyCreditCurlException
     */
    public function testExecuteHttpRequestWithoutHttpMethod()
    {
        $client = oxNew('oxpsEasyCreditHttpClient');
        $client->executeHttpRequest(null, null);
    }

    /**
     * @expectedException oxpsEasyCreditCurlException
     */
    public function testExecuteHttpRequestWithWrongHttpMethod()
    {
        $client = oxNew('oxpsEasyCreditHttpClient');
        $client->executeHttpRequest('PUT', 'https://test.url'); // PUT is not supported by oxpsEasyCreditHttpClient
    }

    /**
     * @expectedException oxpsEasyCreditCurlException
     */
    public function testExecuteHttpRequestWithoutServiceUrl()
    {
        $client = oxNew('oxpsEasyCreditHttpClient');
        $client->executeHttpRequest('GET', null);
    }

    public function testExecuteHttpRequestWithData()
    {
        $expected = '{"success": true}';

        $client = $this->getMock('oxpsEasyCreditHttpClient', array('curl_exec'));
        $client->expects($this->any())->method('curl_exec')->willReturn($expected);

        $this->assertEquals($expected, $client->executeHttpRequest('POST', 'https://test.url', new stdClass()));
    }

}