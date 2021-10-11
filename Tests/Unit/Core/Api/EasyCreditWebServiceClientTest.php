<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Core\Api;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClient;

/**
 * Class EasyCreditWebServiceClientTest
 */
class EasyCreditWebServiceClientTest extends UnitTestCase
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

    public function testSetFunctionOnlyFunction()
    {
        $client = oxNew(EasyCreditWebServiceClient::class);
        $this->assertNull($client->setFunction('test'));
    }

    public function testSetFunctionWithSprintfArgs()
    {
        $sprintfArgs = [
            'p1' => 'v1',
            'p2' => 'v2'
        ];

        $client = oxNew(EasyCreditWebServiceClient::class);
        $this->assertNull($client->setFunction('test', $sprintfArgs));
    }

    public function testSetFunctionWithEmptySprintfArgs()
    {
        $this->expectExceptionMessage("Parameter p2 for curl function test was empty");
        $this->expectException(EasyCreditCurlException::class);
        $sprintfArgs = [
            'p1' => 'v1',
            'p2' => null
        ];

        $client = oxNew(EasyCreditWebServiceClient::class);
        $client->setFunction('test', $sprintfArgs);
    }
}