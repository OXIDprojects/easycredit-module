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
 * Class oxpsEasyCreditLoggingTest
 */
class oxpsEasyCreditLoggingTest extends OxidTestCase
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
        /** @var oxpsEasyCreditLogging $logging */
        $logging = oxNew('oxpsEasyCreditLogging', array());

        // 26 characters output expected
        $this->assertEquals(26, $logging->log('TEST'));
    }

    public function testLogRequestEnabled()
    {
        $logConfig = array(
            oxpsEasyCreditLogging::LOG_CONFIG_LOG_ENABLED => true,
            oxpsEasyCreditLogging::LOG_CONFIG_LOG_DIR => 'test'
        );

        /** @var oxpsEasyCreditLogging $logging */
        $logging = oxNew('oxpsEasyCreditLogging', $logConfig);

        // 377 characters output expected
        $this->assertEquals(377, $logging->logRestRequest('TestRequest', 'TestResponse', 'https://test.url', 350));
    }

    public function testLogRequestDisabled()
    {
        $logConfig = array(
            oxpsEasyCreditLogging::LOG_CONFIG_LOG_ENABLED => false,
            oxpsEasyCreditLogging::LOG_CONFIG_LOG_DIR => 'test'
        );

        /** @var oxpsEasyCreditLogging $logging */
        $logging = oxNew('oxpsEasyCreditLogging', $logConfig);

        // 377 characters output expected
        $this->assertNull($logging->logRestRequest('TestRequest', 'TestResponse', 'https://test.url', 350));
    }
}
