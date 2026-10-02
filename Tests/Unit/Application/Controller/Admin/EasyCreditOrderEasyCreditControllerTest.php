<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderEasyCreditController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditCurlException;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClient;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditRefundMailService;

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
        $response1 = [];
        $expected1 = 'Der Händlerstatus konnte nicht abgefragt werden';

        $stdClass2 = new \stdClass();
        $stdClass2->haendlerstatusV2 = 'IN_ABRECHNUNG';
        $response2 = [0 => $stdClass2];
        $expected2 = 'In Abrechnung';

        $stdClass3 = new \stdClass();
        $stdClass3->haendlerstatusV2 = 'LIEFERUNG_MELDEN';
        $response3 = [0 => $stdClass3];
        $expected3 = 'Lieferung melden';

        $stdClass4 = new \stdClass();
        $stdClass4->haendlerstatusV2 = 'LIEFERUNG_MELDEN_AUSLAUFEND';
        $response4 = [0 => $stdClass4];
        $expected4 = 'Lieferung melden (auslaufend)';

        $expected5 = 'Auslaufend';
        $stdClass5 = new \stdClass();
        $stdClass5->haendlerstatusV2 = 'AUSLAUFEND';
        $response5 = [0 => $stdClass5];

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
        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)->onlyMethods(['getOrderData'])->getMock();
        $tradingApiService->expects($this->once())->method('getOrderData')->with(false)->willReturn($response);

        $controller = $this->getMockBuilder(EasyCreditOrderEasyCreditController::class)
            ->onlyMethods(['getApiService'])
            ->getMock();
        $controller->expects($this->once())->method('getApiService')
            ->willReturn($tradingApiService);

        $this->assertEquals($expected,
                            $controller->getEasyCreditDeliveryState());
    }

    public function sendReversalProvider(): array
    {
        return [
            'accepted by easyCredit'   => [200, null, true],
            'rejected by easyCredit'   => [409, null, false],
            'easyCredit not reachable' => [null, new EasyCreditCurlException('timeout'), false],
        ];
    }

    /**
     * The reversal only counts as done - success message and confirmation mail - once
     * easyCredit accepted it.
     *
     * @dataProvider sendReversalProvider
     */
    public function testSendReversal(?int $statusCode, ?\Exception $exception, bool $expectSuccess): void
    {
        $this->setRequestParameter('reversal', [
            'functionalid' => 'functionalId',
            'amount'       => '19.90',
            'reason'       => 'WIDERRUF_TEILWEISE',
        ]);

        $order = oxNew(Order::class);
        $order->oxorder__oxcurrency = new Field('EUR');

        $apiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendReversal'])
            ->getMock();
        if ($exception) {
            $apiService->expects($this->once())->method('sendReversal')->willThrowException($exception);
        } else {
            $response = new \stdClass();
            $response->statusCode = $statusCode;
            $apiService->expects($this->once())->method('sendReversal')->with(19.90, 'WIDERRUF_TEILWEISE')
                ->willReturn($response);
        }

        $mailService = $this->getMockBuilder(EasyCreditRefundMailService::class)
            ->onlyMethods(['sendRefundMail'])
            ->getMock();
        if ($expectSuccess) {
            $mailService->expects($this->once())->method('sendRefundMail')->with($order, 19.90, 'EUR');
        } else {
            $mailService->expects($this->never())->method('sendRefundMail');
        }

        $controller = $this->getMockBuilder(EasyCreditOrderEasyCreditController::class)
            ->onlyMethods(['validateInput', 'getApiService', 'getRefundMailService', 'getOrder'])
            ->getMock();
        $controller->method('getApiService')->willReturn($apiService);
        $controller->method('getRefundMailService')->willReturn($mailService);
        $controller->method('getOrder')->willReturn($order);

        $controller->sendReversal();

        $viewData = $controller->getViewData();
        $this->assertSame($expectSuccess, isset($viewData['reversalsuccess']));
        $this->assertSame(!$expectSuccess, isset($viewData['reversalerror']));
    }
}
