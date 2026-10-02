<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       easycredit-module
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Core\Domain;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDic;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditOrder;
use OxidSolutionCatalysts\EasyCredit\Model\EasyCreditTradingApiAccess;
use PHPUnit\Framework\TestCase;

/**
 * Delivery report (capture) and paid date of easyCredit orders.
 */
class EasyCreditOrderDeliveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // let OXID build the module chain first, so all _parent aliases exist even if
        // other modules are placed before easyCredit in the chain
        Registry::getUtilsObject()->getClassName(Order::class);
    }

    public function oscSetOrderDeliveredDataProvider(): array
    {
        $paidBefore = '2026-01-01 10:00:00';

        return [
            'v3: delivery report accepted, state not updated yet' => [true, 200, $this->getV3OrderData('REPORT_CAPTURE'), '0000-00-00 00:00:00', true, 'REPORT_CAPTURE'],
            'v3: delivery report rejected'                        => [true, 409, $this->getV3OrderData('REPORT_CAPTURE'), '0000-00-00 00:00:00', false, 'REPORT_CAPTURE'],
            'v3: delivery report rejected, order already billed'  => [true, 409, $this->getV3OrderData('BILLED'), '0000-00-00 00:00:00', true, 'BILLED'],
            'v3: paid date is not overwritten'                    => [true, 200, $this->getV3OrderData('IN_BILLING'), $paidBefore, false, 'IN_BILLING'],
            'v3: no order data available'                         => [true, 200, false, '0000-00-00 00:00:00', true, null],
            'v2: delivery report accepted, state not updated yet' => [false, 200, $this->getV2OrderData('LIEFERUNG_MELDEN'), '0000-00-00 00:00:00', true, 'LIEFERUNG_MELDEN'],
            'v2: no delivery report response, in billing'         => [false, null, $this->getV2OrderData('IN_ABRECHNUNG'), '0000-00-00 00:00:00', true, 'IN_ABRECHNUNG'],
            'v2: no delivery report response'                     => [false, null, $this->getV2OrderData('LIEFERUNG_MELDEN'), '0000-00-00 00:00:00', false, 'LIEFERUNG_MELDEN'],
        ];
    }

    /**
     * @dataProvider oscSetOrderDeliveredDataProvider
     */
    public function testOscSetOrderDelivered(
        bool $isV3,
        ?int $deliveryReportStatusCode,
        $orderData,
        string $paidBefore,
        bool $expectNewPaidDate,
        ?string $expectedDeliveryState
    ): void {
        $deliveryReportResponse = null;
        if ($deliveryReportStatusCode !== null) {
            $deliveryReportResponse = new \stdClass();
            $deliveryReportResponse->statusCode = $deliveryReportStatusCode;
        }

        $tradingApiService = $this->getMockBuilder(EasyCreditTradingApiAccess::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setOrderDeliveredState', 'getOrderData'])
            ->getMock();
        $tradingApiService->expects($this->once())->method('setOrderDeliveredState')->willReturn($deliveryReportResponse);
        $tradingApiService->expects($this->once())->method('getOrderData')->willReturn($orderData);
        UtilsObject::setClassInstance(EasyCreditTradingApiAccess::class, $tradingApiService);

        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEasyCreditUseApiVersionV3'])
            ->getMock();
        $apiConfig->method('getEasyCreditUseApiVersionV3')->willReturn($isV3);
        $dic = $this->getMockBuilder(EasyCreditDic::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getApiConfig'])
            ->getMock();
        $dic->method('getApiConfig')->willReturn($apiConfig);

        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDic', 'save'])
            ->getMock();
        $order->method('getDic')->willReturn($dic);
        $order->expects($this->once())->method('save');
        $order->oxorder__ecredisv3order = new Field($isV3 ? 1 : 0);
        $order->oxorder__oxpaid = new Field($paidBefore);

        $order->oscSetOrderDelivered();

        UtilsObject::resetClassInstances();

        if ($expectNewPaidDate) {
            $this->assertNotSame('0000-00-00 00:00:00', $order->oxorder__oxpaid->value);
            $this->assertNotSame($paidBefore, $order->oxorder__oxpaid->value);
        } else {
            $this->assertSame($paidBefore, $order->oxorder__oxpaid->value);
        }
        $this->assertSame($expectedDeliveryState, $order->oxorder__ecreddeliverystate->value);
    }

    private function getV3OrderData(string $status): \stdClass
    {
        $orderData = new \stdClass();
        $orderData->status = $status;

        return $orderData;
    }

    private function getV2OrderData(string $status): array
    {
        $orderData = new \stdClass();
        $orderData->haendlerstatusV2 = $status;

        return [$orderData];
    }
}
