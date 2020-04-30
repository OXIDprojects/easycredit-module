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
 * Class oxpsEasyCreditWebServiceClientTest
 */
class oxpsEasyCreditWebServiceClientTest extends OxidTestCase
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

    public function testSetFunctionOnlyFunction()
    {
        $client = oxNew('oxpsEasyCreditWebServiceClient');
        $this->assertNull($client->setFunction('test'));
    }

    public function testSetFunctionWithSprintfArgs()
    {
        $sprintfArgs = array(
            'p1' => 'v1',
            'p2' => 'v2'
        );

        $client = oxNew('oxpsEasyCreditWebServiceClient');
        $this->assertNull($client->setFunction('test', $sprintfArgs));
    }

    /**
     * @expectedException oxpsEasyCreditCurlException
     * @expectedExceptionMessage Parameter p2 for curl function test was empty
     */
    public function testSetFunctionWithEmptySprintfArgs()
    {
        $sprintfArgs = array(
            'p1' => 'v1',
            'p2' => null
        );

        $client = oxNew('oxpsEasyCreditWebServiceClient');
        $client->setFunction('test', $sprintfArgs);
    }
}