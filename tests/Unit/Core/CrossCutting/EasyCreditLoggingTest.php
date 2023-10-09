<?php

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Core\CrossCutting;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\EasyCredit\Core\CrossCutting\EasyCreditLogging;

/**
 * Class EasyCreditLoggingTest
 */
class EasyCreditLoggingTest extends UnitTestCase
{
    /**
     * Set up test environment
     *
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testLog()
    {
        /** @var EasyCreditLogging $logging */
        $logging = oxNew(EasyCreditLogging::class, []);

        // 26 characters output expected
        $this->assertEquals(26, $logging->log('TEST'));
    }

    public function testLogRequestEnabled()
    {
        $logConfig = [
            EasyCreditLogging::LOG_CONFIG_LOG_ENABLED => true,
            EasyCreditLogging::LOG_CONFIG_LOG_DIR => 'test'
        ];

        /** @var EasyCreditLogging $logging */
        $logging = oxNew(EasyCreditLogging::class, $logConfig);

        // 377 characters output expected
        $this->assertEquals(377, $logging->logRestRequest('TestRequest', 'TestResponse', 'https://test.url', 350));
    }

    public function testLogRequestDisabled()
    {
        $logConfig = [
            EasyCreditLogging::LOG_CONFIG_LOG_ENABLED => false,
            EasyCreditLogging::LOG_CONFIG_LOG_DIR => 'test'
        ];

        /** @var EasyCreditLogging $logging */
        $logging = oxNew(EasyCreditLogging::class, $logConfig);

        // 377 characters output expected
        $this->assertNull($logging->logRestRequest('TestRequest', 'TestResponse', 'https://test.url', 350));
    }
}
