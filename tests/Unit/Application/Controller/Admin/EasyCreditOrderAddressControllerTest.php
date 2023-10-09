<?php

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin\EasyCreditOrderAddressController;

/**
 * Class EasyCreditOrderAddressControllerTest
 */
class EasyCreditOrderAddressControllerTest extends UnitTestCase
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
        $this->assertEquals('order_address.tpl', $controller->render());
    }

    public function testRenderWithEditObjectId(): void
    {
        $controller = $this->getMock(EasyCreditOrderAddressController::class, ['getEditObjectId']);
        $controller->expects($this->any())->method('getEditObjectId')->willReturn('1');

        $this->assertEquals('order_address.tpl', $controller->render());
    }
}
