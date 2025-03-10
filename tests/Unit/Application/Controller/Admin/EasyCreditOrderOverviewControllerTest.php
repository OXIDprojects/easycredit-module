<?php


namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Controller\Admin;


use Mollie\Payment\Application\Model\PaymentConfig;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\UtilsObject;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderOverviewController;
use OxidSolutionCatalysts\EasyCredit\Model\EasyCreditTradingApiAccess;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;

class EasyCreditOrderOverviewControllerTest extends TestCase
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
        $expected = 'testresult';

        $order = oxNew(Order::class);
        $order->oxorder__functionalid = new Field('functionalId');

        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->onlyMethods(['getOrderState'])
            ->setConstructorArgs([$order])
            ->getMock();
        $tradingApiService->expects($this->once())->method('getOrderState')->willReturn($expected);

        UtilsObject::setClassInstance(EasyCreditTradingApiAccess::class, $tradingApiService);

        $controller = oxNew(EasyCreditOrderOverviewController::class);

        $this->assertEquals($expected, $controller->getDeliveryState($order));

        UtilsObject::resetClassInstances();
    }

    public function testSendOrderNoOrder()
    {
        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['getEditObjectId'])
            ->getMock();

        $controller->expects($this->exactly(2))
            ->method('getEditObjectId')
            ->willReturn(null);

        $this->assertNull($controller->sendOrder());
    }

    public function testSendOrderWithOrder()
    {
        $order = oxNew(Order::class);
        $order->oxorder__ecredfunctionalid = new Field('functionalId');

        $orderData                   = new \stdClass();
        $orderData->haendlerstatusV2 = 'returnstate';
        $state                       = [0 => $orderData];

        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->setConstructorArgs([$order])
            ->onlyMethods(['setOrderDeliveredState', 'getOrderData'])
            ->getMock();
        $tradingApiService->expects($this->once())->method('setOrderDeliveredState')->willReturn(null);
        $tradingApiService->expects($this->once())->method('getOrderData')->willReturn($state);

        UtilsObject::setClassInstance(EasyCreditTradingApiAccess::class, $tradingApiService);

        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['loadOrder'])
            ->getMock();

        $controller->expects($this->once())->method('loadOrder')->willReturn($order);

        $controller->sendOrder();

        UtilsObject::resetClassInstances();
    }

    public function testSendOrderNoECOrder()
    {
        $order = oxNew(Order::class);

        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->setConstructorArgs([$order])
            ->onlyMethods(['setOrderDeliveredState'])
            ->getMock();
        $tradingApiService->expects($this->never())->method('setOrderDeliveredState');

        $controller = oxNew(EasyCreditOrderOverviewController::class);
        $controller->sendOrder();
    }
}