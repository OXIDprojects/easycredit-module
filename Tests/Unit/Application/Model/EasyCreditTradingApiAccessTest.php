<?php


namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Model;


use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClient;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;

class EasyCreditTradingApiAccessTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetOrderData()
    {
        $return = json_decode('{"wsMessages": {"messages": []},"uuid": "d86a5797-a209-4648-ae77-8a594c337aed","ergebnisse": [{"haendlerstatusV2": "IN_ABRECHNUNG"}]}');
        $expected = new \stdClass();
        $expected->haendlerstatusV2 = "IN_ABRECHNUNG";
        $order = oxNew(Order::class);
        $order->oxorder__ecredfunctionalid = new Field('functionalId');

        $dic = EasyCreditDicFactory::getDic();

        $wsc = $this->getMockBuilder(EasyCreditWebServiceClient::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()->onlyMethods(['execute'])
            ->getMock();
        $wsc->expects($this->once())->method('execute')->willReturn($return);

        $model = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->setConstructorArgs([$order])
            ->onlyMethods(['getService'])
            ->getMock();

        $model->expects($this->once())->method('getService')
            ->with(
                EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V2_DELIVERY_STATE,
                $dic,
                ['functionalId'],
                [],
                true
            )->willReturn($wsc);

        $this->assertEquals( [$expected], $model->getOrderData());
    }

    public function testGetOrderStateErrorState()
    {
        $model = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods(['getOrderData'])->getMock();
        $model->expects($this->once())->method('getOrderData')->willReturn([]);

        $this->assertEquals('Der Händlerstatus konnte nicht abgefragt werden', $model->getOrderState());
    }

    public function testGetOrderStateValidState()
    {
        $return = new \stdClass();
        $return->haendlerstatusV2 = "IN_ABRECHNUNG";

        $model = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods(['getOrderData'])->getMock();
        $model->expects($this->once())->method('getOrderData')->willReturn([$return]);

        $this->assertEquals('In Abrechnung', $model->getOrderState());
    }
}