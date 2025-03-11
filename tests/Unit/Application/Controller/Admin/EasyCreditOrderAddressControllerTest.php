<?php

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Controller\Admin;

use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderAddressController;

/**
 * Class EasyCreditOrderAddressControllerTest
 */
class EasyCreditOrderAddressControllerTest extends TestCase
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

    public function testRender(): void
    {
        $controller = oxNew(EasyCreditOrderAddressController::class);
        $this->assertEquals('order_address', $controller->render());
    }

    public function testRenderWithEditObjectId(): void
    {
        $controller = $this->getMockBuilder(EasyCreditOrderAddressController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEditObjectId'])
            ->getMock();
        $controller->expects($this->any())->method('getEditObjectId')->willReturn('1');

        $this->assertEquals('order_address', $controller->render());
    }
}
