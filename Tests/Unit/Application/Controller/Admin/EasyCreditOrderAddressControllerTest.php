<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderAddressController;

/**
 * Class EasyCreditOrderAddressControllerTest
 */
class EasyCreditOrderAddressControllerTest extends UnitTestCase
{
    /**
     * Set up test environment
     *
     * @return null
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     * @return null
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
