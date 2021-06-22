<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderEasyCreditController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClient;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;

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
        $controller = $this->getMockBuilder(EasyCreditOrderEasyCreditController::class)
            ->onlyMethods(['getEditObjectId', 'hasEasyCreditPayment', 'getEasyCreditDeliveryState'])
            ->getMock();
        $controller->expects($this->any())->method('getEditObjectId')->willReturn('1');
        $controller->expects($this->any())->method('hasEasyCreditPayment')->willReturn(true);
        $controller->expects($this->any())->method('getEasyCreditDeliveryState')->willReturn('text');

        $this->assertEquals('oxpseasycredit_order_easycredit.tpl', $controller->render());
    }

    public function testGetEasyCreditConfirmationResponse(): void
    {
        $response         = new \stdClass();
        $response->result = 'test';

        $order                                = oxNew(Order::class);
        $order->oxorder__ecredconfirmresponse = new Field(base64_encode(serialize($response)));

        $controller = $this->getMockBuilder(EasyCreditOrderEasyCreditController::class)->onlyMethods(['getOrder'])->getMock();
        $controller->expects($this->any())->method('getOrder')->willReturn($order);

        $this->assertEquals('{
    "result": "test"
}', $controller->getEasyCreditConfirmationResponse());
    }

    public function deliveryStateDataProvider()
    {
        $response1 = '{"wsMessages": {"messages": []},"uuid": "a6e08a9a-9a81-40ae-9a2d-33df493b3ddb","ergebnisse": []}';
        $expected1 = 'Der Händlerstatus konnte nicht abgefragt werden';
        $response2 = '{"wsMessages": {"messages": []},"uuid": "d86a5797-a209-4648-ae77-8a594c337aed","ergebnisse": [{"haendlerstatusV2": "IN_ABRECHNUNG"}]}';
        $expected2 = 'In Abrechnung';
        $response3 = '{"wsMessages": {"messages": []},"uuid": "d86a5797-a209-4648-ae77-8a594c337aed","ergebnisse": [{"haendlerstatusV2": "LIEFERUNG_MELDEN"}]}';
        $expected3 = 'Lieferung melden';
        $response4 = '{"wsMessages": {"messages": []},"uuid": "d86a5797-a209-4648-ae77-8a594c337aed","ergebnisse": [{"haendlerstatusV2": "LIEFERUNG_MELDEN_AUSLAUFEND"}]}';
        $expected4 = 'Lieferung melden (auslaufend)';
        $response5 = '{"wsMessages": {"messages": []},"uuid": "d86a5797-a209-4648-ae77-8a594c337aed","ergebnisse": [{"haendlerstatusV2": "AUSLAUFEND"}]}';
        $expected5 = 'Auslaufend';

        return [
            [$response1, $expected1],
            [$response2, $expected2],
            [$response3, $expected3],
            [$response4, $expected4],
            [$response5, $expected5],
        ];
    }

    /**
     * @dataProvider deliveryStateDataProvider
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     */
    public function testGetEasyCreditDeliveryState($response, $expected)
    {
        $order = oxNew(Order::class);
        $response = json_decode($response);

        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)->onlyMethods(['getOrderData'])->getMock();
        $tradingApiService->expects($this->once())->method('getOrderData')->with($order)->willReturn($response);

        $controller = $this->getMockBuilder(EasyCreditOrderEasyCreditController::class)
            ->onlyMethods(['getService','getOrder'])
            ->getMock();
        $controller->expects($this->once())->method('getService')
            ->willReturn($tradingApiService);
        $controller->expects($this->once())->method('getOrder')
            ->willReturn($tradingApiService);

        $this->assertEquals($expected,
                            $controller->getEasyCreditDeliveryState());
    }
}
