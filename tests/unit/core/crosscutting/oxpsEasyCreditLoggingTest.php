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
 * Class EasyCreditLoggingTest
 */
class EasyCreditLoggingTest extends OxidTestCase
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

    public function testLog()
    {
        /** @var EasyCreditLogging $logging */
        $logging = oxNew(\OxidProfessionalServices\EasyCredit\Core\CrossCutting\EasyCreditLogging::class, array());

        // 26 characters output expected
        $this->assertEquals(26, $logging->log('TEST'));
    }

    public function testLogRequestEnabled()
    {
        $logConfig = array(
            EasyCreditLogging::LOG_CONFIG_LOG_ENABLED => true,
            EasyCreditLogging::LOG_CONFIG_LOG_DIR => 'test'
        );

        /** @var EasyCreditLogging $logging */
        $logging = oxNew('EasyCreditLogging', $logConfig);

        // 377 characters output expected
        $this->assertEquals(377, $logging->logRestRequest('TestRequest', 'TestResponse', 'https://test.url', 350));
    }

    public function testLogRequestDisabled()
    {
        $logConfig = array(
            EasyCreditLogging::LOG_CONFIG_LOG_ENABLED => false,
            EasyCreditLogging::LOG_CONFIG_LOG_DIR => 'test'
        );

        /** @var EasyCreditLogging $logging */
        $logging = oxNew('EasyCreditLogging', $logConfig);

        // 377 characters output expected
        $this->assertNull($logging->logRestRequest('TestRequest', 'TestResponse', 'https://test.url', 350));
    }
}
