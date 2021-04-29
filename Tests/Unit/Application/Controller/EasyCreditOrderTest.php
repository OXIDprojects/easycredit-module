<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ViewConfig;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicConfig;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditOrder;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditSession;
use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;
use OxidProfessionalServices\EasyCredit\Core\PayLoad\EasyCreditPayloadFactory;

/**
 * Class EasyCreditOrderTest
 */
class EasyCreditOrderTest extends UnitTestCase
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
        $mockOxConfig = $this->getMock(Registry::getConfig(), [], []);

        $session = oxNew(EasyCreditDicSession::class, $oxSession);
        $mockApiConfig = oxNew(EasyCreditApiConfig::class, oxpsEasyCreditDicFactory::getApiConfigArray());
        $mockLogging = $this->getMock(EasyCreditLogging::class, [], [[]]);
        $mockPayloadFactory = $this->getMock(EasyCreditPayloadFactory::class, [], []);
        $mockDicConfig = $this->getMock(EasyCreditDicConfig::class, [], [$mockOxConfig]);

        $mockDic = oxNew(
            EasyCreditDic::class,
            $session,
            $mockApiConfig,
            $mockPayloadFactory,
            $mockLogging,
            $mockDicConfig
        );

        return $mockDic;
    }

    public function testGetPaymentNoEasyCredit()
    {
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
        $payment->setId(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMock(EasyCreditOrder::class, ['getDic', 'parentGetPayment']);
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
        $payment->setId(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMock(EasyCreditOrder::class, ['getDic', 'parentGetPayment']);
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
        $payment->setId(EasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $viewConfig = $this->getMock(ViewConfig::class, ['getModulePath']);
        $viewConfig->expects($this->any())->method('getModulePath')->willThrowException(new \Exception('TEST'));

        $order = $this->getMock(EasyCreditOrder::class, ['getDic', 'parentGetPayment', 'getViewConfig']);
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);
        $order->expects($this->any())->method('getViewConfig')->willReturn($viewConfig);

        $this->assertNull($order->getPayment());
    }

    public function testGetPaymentNoStorage()
    {
        $this->expectException(PHPUnit_Framework_Error_Warning::class);
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $payment = oxNew(Payment::class);
        $payment->oxpayments__oxdesc = new Field('test payment');
        $payment->setId(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMock(EasyCreditOrder::class, ['getDic', 'parentGetPayment']);
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

        $order = $this->getMock(EasyCreditOrder::class, ['getDic']);
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($tilgungsplanTxt, $order->getTilgungsplanText());
    }

    public function testGetTilgungsplanTextEmpty()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $order = $this->getMock(EasyCreditOrder::class, ['getDic']);
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($order->getTilgungsplanText());
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

        $order = $this->getMock(EasyCreditOrder::class, ['getDic']);
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($url, $order->getUrlVorvertraglicheInformationen());
    }

    public function testGetUrlVorvertraglicheInformationenEmpty()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $order = $this->getMock(EasyCreditOrder::class, ['getDic']);
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

        $order = $this->getMock(EasyCreditOrder::class, ['getDic']);
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($text, $order->getPaymentPlanTxt());
    }

    public function testGetPaymentPlanTxtEmpty()
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $order = $this->getMock(EasyCreditOrder::class, ['getDic']);
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($order->getPaymentPlanTxt());
    }

    public function testGetPaymentPlanTxtEmptyStandardDic()
    {
        $order = oxNew(Order::class);
        $this->assertNull($order->getPaymentPlanTxt());
    }
}