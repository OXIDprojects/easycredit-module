<?php


namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\OrderOverview;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderOverviewController;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditOrder;

class EasyCreditOrderOverviewControllerTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // let OXID build the module chain first, so all _parent aliases exist even if
        // other modules are placed before easyCredit in the chain
        Registry::getUtilsObject()->getClassName(OrderOverview::class);
        Registry::getUtilsObject()->getClassName(Order::class);
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
        // the number of calls depends on the other modules in the OrderOverview chain
        $controller->expects($this->atLeastOnce())
            ->method('getEditObjectId')
            ->willReturn(null);
        $this->assertNull($controller->sendOrder());
    }

    public function testSendOrderWithOrder()
    {
        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['oscSetOrderDelivered'])
            ->getMock();
        $order->oxorder__ecredfunctionalid = new Field('functionalId');
        $order->expects($this->once())->method('oscSetOrderDelivered');

        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['loadOrder'])
            ->getMock();
        $controller->expects($this->once())->method('loadOrder')->willReturn($order);

        $controller->sendOrder();
    }

    public function testSendOrderNoECOrder()
    {
        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['oscSetOrderDelivered'])
            ->getMock();
        $order->expects($this->never())->method('oscSetOrderDelivered');

        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['loadOrder'])
            ->getMock();
        $controller->expects($this->once())->method('loadOrder')->willReturn($order);

        $controller->sendOrder();
    }
}
