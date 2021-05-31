<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderEasyCreditController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;

/**
 * Class EasyCreditOrderEasyCreditControllerTest
 */
class EasyCreditOrderEasyCreditControllerTest extends UnitTestCase
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
        $controller = oxNew(EasyCreditOrderEasyCreditController::class);
        $this->assertEquals('oxpseasycredit_order_easycredit.tpl', $controller->render());
    }

    public function testRenderWithEditObjectId(): void
    {
        $controller = $this->getMock(EasyCreditOrderEasyCreditController::class, ['getEditObjectId', 'hasEasyCreditPayment']);
        $controller->expects($this->any())->method('getEditObjectId')->willReturn('1');
        $controller->expects($this->any())->method('hasEasyCreditPayment')->willReturn(true);

        $this->assertEquals('oxpseasycredit_order_easycredit.tpl', $controller->render());
    }

    public function testGetEasyCreditConfirmationResponse(): void
    {
        $response = new \stdClass();
        $response->result = 'test';

        $order = oxNew(Order::class);
        $order->oxorder__ecredconfirmresponse = new Field(base64_encode(serialize($response)));

        $controller = $this->getMock(EasyCreditOrderEasyCreditController::class, ['getOrder']);
        $controller->expects($this->any())->method('getOrder')->willReturn($order);

        $this->assertEquals('{
    "result": "test"
}', $controller->getEasyCreditConfirmationResponse());
    }
}
