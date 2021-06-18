<?php


namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;


use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderOverviewController;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;

class EasyCreditOrderOverviewControllerTest extends UnitTestCase
{
    public function testGetDeliveryState()
    {
        $result        = 'testresult';
        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['callService'])->getMock();
        $controller->expects($this->once())
            ->method('callService')
            ->with(EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_STATE, 'functId')
            ->willReturn($result);
        $this->assertEquals($result, $controller->getDeliveryState('functId'));
    }

    public function testSendOrderNoOrder()
    {
        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['callService', 'getEditObjectId'])->getMock();
        $controller->expects($this->never())
            ->method('callService');
        $controller->expects($this->exactly(2))
            ->method('getEditObjectId')
            ->willReturn(null);
        $this->assertNull($controller->sendOrder());
    }

    public function testSendOrderWithOrder()
    {
        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['callService','loadFunctionalIdFromOrder'])->getMock();
        $controller->expects($this->once())
            ->method('loadFunctionalIdFromOrder')
            ->willReturn('functId');
        $controller->expects($this->once())->method('callService')->with(
            EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_REPORT,
            'functId'
        );

        $controller->sendOrder();
    }

    public function testSendOrderNoECOrder()
    {
        $controller = $this->getMockBuilder(EasyCreditOrderOverviewController::class)
            ->onlyMethods(['callService','loadFunctionalIdFromOrder'])->getMock();
        $controller->expects($this->once())
            ->method('loadFunctionalIdFromOrder')
            ->willReturn(null);
        $controller->expects($this->never())->method('callService');

        $controller->sendOrder();
    }
}