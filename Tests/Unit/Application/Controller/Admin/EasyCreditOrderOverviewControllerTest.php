<?php


namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderOverviewController;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;

class EasyCreditOrderOverviewControllerTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetDeliveryState()
    {
        $result        = 'testresult';
        $order = oxNew(Order::class);
        $order->oxorder__functionalid = new Field('functionalId');
        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->onlyMethods(['getOrderState'])
            ->setConstructorArgs([$order])
            ->getMock();
        $tradingApiService->expects($this->once())->method('getOrderState')->willReturn($result);

        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['getService'])->getMock();
        $controller->expects($this->once())
            ->method('getService')
            ->willReturn($tradingApiService);
        $this->assertEquals($result, $controller->getDeliveryState($order));
    }

    public function testSendOrderNoOrder()
    {
        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['getService', 'getEditObjectId'])->getMock();
        $controller->expects($this->never())
            ->method('getService');
        $controller->expects($this->exactly(2))
            ->method('getEditObjectId')
            ->willReturn(null);
        $this->assertNull($controller->sendOrder());
    }

    public function testSendOrderWithOrder()
    {
        $order = oxNew(Order::class);
        $order->oxorder__functionalid = new Field('functionalId');

        $orderData                   = new \stdClass();
        $orderData->haendlerstatusV2 = 'returnstate';
        $state                       = [0 => $orderData];

        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->setConstructorArgs([$order])
            ->onlyMethods(['setOrderDeliveredState', 'getOrderData'])
            ->getMock();
        $tradingApiService->expects($this->once())->method('setOrderDeliveredState')->willReturn(null);
        $tradingApiService->expects($this->once())->method('getOrderData')->willReturn($state);
        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['getService','loadFunctionalIdFromOrder','loadOrder'])->getMock();
        $controller->expects($this->once())
            ->method('loadOrder')
            ->willReturn($order);
        $controller->expects($this->once())
            ->method('loadFunctionalIdFromOrder')
            ->willReturn('functionalId');
        $controller->expects($this->once())->method('getService')->willReturn($tradingApiService);

        $controller->sendOrder();
    }

    public function testSendOrderNoECOrder()
    {
        $order = oxNew(Order::class);

        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->setConstructorArgs([$order])
            ->onlyMethods(['setOrderDeliveredState'])
            ->getMock();
        $tradingApiService->expects($this->never())->method('setOrderDeliveredState');

        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['getService','loadFunctionalIdFromOrder'])->getMock();
        $controller->expects($this->once())
            ->method('loadFunctionalIdFromOrder')
            ->willReturn(null);

        $controller->expects($this->never())->method('getService');

        $controller->sendOrder();
    }
}