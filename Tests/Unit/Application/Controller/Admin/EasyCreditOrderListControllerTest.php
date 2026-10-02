<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2026
 */

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\OrderList;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderListController;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditRefundMailService;

/**
 * Cancelling an easyCredit order in the backend: optional reversal at easyCredit and the
 * confirmation mail.
 */
class EasyCreditOrderListControllerTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // let OXID build the module chain first, so all _parent aliases exist even if
        // other modules are placed before easyCredit in the chain
        Registry::getUtilsObject()->getClassName(OrderList::class);
        Registry::getUtilsObject()->getClassName(Order::class);
    }

    public function testCancelOrderOfOtherPaymentSendsNoMail(): void
    {
        $mailService = $this->getMailServiceMock();
        $mailService->expects($this->never())->method('sendCancelMail');

        $controller = $this->getControllerMock(null, false, null, $mailService);
        $controller->expects($this->never())->method('getEasyCreditTradingApiService');

        $controller->cancelOrder();
    }

    public function testCancelOrderWithoutAutomatedRefund(): void
    {
        $order = $this->getOrder();

        $mailService = $this->getMailServiceMock();
        $mailService->expects($this->once())->method('sendCancelMail')->with($order, null, 'EUR');

        $controller = $this->getControllerMock($order, false, null, $mailService);
        $controller->expects($this->never())->method('getEasyCreditTradingApiService');

        $controller->cancelOrder();
    }

    public function testCancelOrderWithAcceptedRefund(): void
    {
        $order = $this->getOrder();

        $tradingApiService = $this->getTradingApiServiceMock(149.989, 200);
        $tradingApiService->expects($this->once())->method('sendReversal')
            ->with(149.99, EasyCreditTradingApiAccess::REVERSAL_REASON_FULL);

        $mailService = $this->getMailServiceMock();
        $mailService->expects($this->once())->method('sendCancelMail')->with($order, 149.99, 'EUR');

        $controller = $this->getControllerMock($order, true, $tradingApiService, $mailService);
        $controller->expects($this->never())->method('onEasyCreditRefundOnCancelFailed');

        $controller->cancelOrder();
    }

    public function testCancelOrderWithRejectedRefund(): void
    {
        $order = $this->getOrder();

        $tradingApiService = $this->getTradingApiServiceMock(149.99, 409);

        $mailService = $this->getMailServiceMock();
        $mailService->expects($this->once())->method('sendCancelMail')->with($order, null, 'EUR');

        $controller = $this->getControllerMock($order, true, $tradingApiService, $mailService);
        $controller->expects($this->once())->method('onEasyCreditRefundOnCancelFailed');

        $controller->cancelOrder();
    }

    public function testCancelOrderWithNothingLeftToRefund(): void
    {
        $order = $this->getOrder();

        $tradingApiService = $this->getTradingApiServiceMock(0.0, 200);
        $tradingApiService->expects($this->never())->method('sendReversal');

        $mailService = $this->getMailServiceMock();
        $mailService->expects($this->once())->method('sendCancelMail')->with($order, null, 'EUR');

        $controller = $this->getControllerMock($order, true, $tradingApiService, $mailService);

        $controller->cancelOrder();
    }

    private function getOrder(): Order
    {
        $order = oxNew(Order::class);
        $order->oxorder__oxcurrency = new Field('EUR');

        return $order;
    }

    private function getMailServiceMock()
    {
        return $this->getMockBuilder(EasyCreditRefundMailService::class)
            ->onlyMethods(['sendCancelMail'])
            ->getMock();
    }

    private function getTradingApiServiceMock(float $currentOrderValue, int $statusCode)
    {
        $response = new \stdClass();
        $response->statusCode = $statusCode;

        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCurrentOrderValue', 'sendReversal'])
            ->getMock();
        $tradingApiService->method('getCurrentOrderValue')->willReturn($currentOrderValue);
        $tradingApiService->method('sendReversal')->willReturn($response);

        return $tradingApiService;
    }

    private function getControllerMock(?Order $order, bool $automatedRefund, $tradingApiService, $mailService)
    {
        $controller = $this->getMockBuilder(EasyCreditOrderListController::class)
            ->onlyMethods([
                'loadEasyCreditOrderForCancel',
                'isEasyCreditAutomatedRefundOnCancel',
                'getEasyCreditTradingApiService',
                'getEasyCreditRefundMailService',
                'onEasyCreditRefundOnCancelFailed',
                'getEditObjectId',
                'init',
            ])
            ->getMock();
        // no edit object id: the core cancellation finds no order and leaves the database alone
        $controller->method('getEditObjectId')->willReturn(null);
        $controller->method('loadEasyCreditOrderForCancel')->willReturn($order);
        $controller->method('isEasyCreditAutomatedRefundOnCancel')->willReturn($automatedRefund);
        $controller->method('getEasyCreditRefundMailService')->willReturn($mailService);
        if ($tradingApiService) {
            $controller->method('getEasyCreditTradingApiService')->willReturn($tradingApiService);
        }

        return $controller;
    }
}
