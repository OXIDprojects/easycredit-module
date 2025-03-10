<?php

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Controller;

use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\ViewConfig;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Controller\EasyCreditOrderController;
use OxidSolutionCatalysts\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDic;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicSession;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditOrder;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditPayment;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditSession;
use OxidSolutionCatalysts\EasyCredit\Core\Dto\EasyCreditStorage;

/**
 * Class EasyCreditOrderTest
 */
class EasyCreditOrderTest extends TestCase
{
    /**
     * Set up test environment
     *
     * @return null
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     * @return null
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    protected function buildDic($oxSession)
    {
        $mockOxConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

        $session = oxNew(EasyCreditDicSession::class, $oxSession);
        $mockApiConfig = oxNew(EasyCreditApiConfig::class, EasyCreditDicFactory::getApiConfigArray());
        $mockLogging = $this->getMockBuilder(EasyCreditLogging::class)->disableOriginalConstructor()->getMock();
        $mockDicConfig = $this->getMockBuilder(EasyCreditDicConfig::class)->disableOriginalConstructor()->getMock();

        $mockDic = oxNew(
            EasyCreditDic::class,
            $session,
            $mockApiConfig,
            $mockLogging,
            $mockDicConfig
        );

        return $mockDic;
    }

    public function testGetPaymentNoEasyCredit()
    {
        $this->markTestSkipped('Empty order has no initialized payment');
        $order = oxNew(Order::class);
        $this->assertNotNull($order->getPayment());
    }

    public function testGetPaymentEasyCreditWithoutPaymentPlan()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $payment = oxNew(Payment::class);
        $payment->setId(EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
                'parentGetPayment',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);

        $this->assertNull($order->getPayment());
    }

    public function testGetPaymentEasyCredit()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $text = 'payment plan';
        $storage->setRatenplanTxt($text);
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $payment = oxNew(Payment::class);
        $payment->oxpayments__oxdesc = new Field('test payment');
        $payment->setId(EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
                'parentGetPayment',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);

        $this->assertNull($order->getPayment());
    }

    public function testGetPaymentEasyCreditNoLogo()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $text = 'payment plan';
        $storage->setRatenplanTxt($text);
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $payment = oxNew(Payment::class);
        $payment->oxpayments__oxdesc = new Field('test payment');
        $payment->setId(EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $viewConfig = $this->getMockBuilder(ViewConfig::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getModulePath',
            ])
            ->getMock();
        $viewConfig->expects($this->any())->method('getModulePath')->willThrowException(new \Exception('TEST'));

        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
                'parentGetPayment',
                'getViewConfig',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);
        $order->expects($this->any())->method('getViewConfig')->willReturn($viewConfig);

        $this->assertNull($order->getPayment());
    }

    public function testGetPaymentNoStorage()
    {
        //$this->expectException(PHPUnit_Framework_Error_Warning::class);
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $payment = oxNew(Payment::class);
        $payment->oxpayments__oxdesc = new Field('test payment');
        $payment->setId(EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
                'parentGetPayment',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);

        $this->assertNull($order->getPayment());
    }

    public function testGetTilgungsplanText()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $tilgungsplanTxt = 'TilgungsplanText';
        $storage->setTilgungsplanTxt($tilgungsplanTxt);
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($tilgungsplanTxt, $order->getTilgungsplanTxt());
    }

    public function testGetTilgungsplanTextEmpty()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $order = $this->getMockBuilder(EasyCreditOrder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($order->getTilgungsplanTxt());
    }

    public function testGetUrlVorvertraglicheInformationen()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $url = 'https://test.url';
        $allgemeineVorgangsdaten = new \stdClass();
        $allgemeineVorgangsdaten->urlVorvertraglicheInformationen = $url;
        $storage->setAllgemeineVorgangsdaten($allgemeineVorgangsdaten);
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $order = $this->getMockBuilder(EasyCreditOrderController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($url, $order->getUrlVorvertraglicheInformationen());
    }

    public function testGetUrlVorvertraglicheInformationenEmpty()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $order = $this->getMockBuilder(EasyCreditOrderController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($order->getUrlVorvertraglicheInformationen());
    }

    public function testGetPaymentPlanTxt()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $text = 'payment plan';
        $storage->setRatenplanTxt($text);
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $order = $this->getMockBuilder(EasyCreditOrderController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($text, $order->getPaymentPlanTxt());
    }

    public function testGetPaymentPlanTxtEmpty()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $order = $this->getMockBuilder(EasyCreditOrderController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
            ])
            ->getMock();
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($order->getPaymentPlanTxt());
    }

    public function testGetPaymentPlanTxtEmptyStandardDic()
    {
        $order = oxNew(OrderController::class);
        $this->assertNull($order->getPaymentPlanTxt());
    }
}