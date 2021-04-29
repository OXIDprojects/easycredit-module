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
 * @package       easycredit
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

/**
 * Class oxpsEasyCreditOrderTest
 */
class oxpsEasyCreditOrderTest extends OxidTestCase
{
    /**
     * Set up test environment
     *
     * @return null
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     * @return null
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    protected function buildDic($oxSession)
    {
        $mockOxConfig = $this->getMock('oxConfig', array(), array());

        $session = oxNew('EasyCreditDicSession', $oxSession);
        $mockApiConfig = oxNew('oxpsEasyCreditApiConfig', oxpsEasyCreditDicFactory::getApiConfigArray());
        $mockLogging = $this->getMock('EasyCreditLogging', array(), array(array()));
        $mockPayloadFactory = $this->getMock('EasyCreditPayloadFactory', array(), array());
        $mockDicConfig = $this->getMock('EasyCreditDicConfig', array(), array($mockOxConfig));

        $mockDic = oxNew(
            'oxpseasycreditdic',
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
        $order = oxNew('order');
        $this->assertNotNull($order->getPayment());
    }

    public function testGetPaymentEasyCreditWithoutPaymentPlan()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $payment = oxNew('oxpayment');
        $payment->setId(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMock('oxpseasycreditorder', array('getDic', 'parentGetPayment'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);

        $this->assertNull($order->getPayment());
    }

    public function testGetPaymentEasyCredit()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $text = 'payment plan';
        $storage->setRatenplanTxt($text);
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $payment = oxNew('oxpayment');
        $payment->oxpayments__oxdesc = new oxField('test payment');
        $payment->setId(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMock('oxpseasycreditorder', array('getDic', 'parentGetPayment'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);

        $this->assertNull($order->getPayment());
    }

    public function testGetPaymentEasyCreditNoLogo()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $text = 'payment plan';
        $storage->setRatenplanTxt($text);
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $payment = oxNew('oxpayment');
        $payment->oxpayments__oxdesc = new oxField('test payment');
        $payment->setId(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $viewConfig = $this->getMock('oxViewConfig', array('getModulePath'));
        $viewConfig->expects($this->any())->method('getModulePath')->willThrowException(new Exception('TEST'));

        $order = $this->getMock('oxpseasycreditorder', array('getDic', 'parentGetPayment', 'getViewConfig'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);
        $order->expects($this->any())->method('getViewConfig')->willReturn($viewConfig);

        $this->assertNull($order->getPayment());
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testGetPaymentNoStorage()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $payment = oxNew('oxpayment');
        $payment->oxpayments__oxdesc = new oxField('test payment');
        $payment->setId(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $order = $this->getMock('oxpseasycreditorder', array('getDic', 'parentGetPayment'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);
        $order->expects($this->any())->method('parentGetPayment')->willReturn($payment);

        $this->assertNull($order->getPayment());
    }

    public function testGetTilgungsplanText()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $tilgungsplanTxt = 'TilgungsplanText';
        $storage->setTilgungsplanTxt($tilgungsplanTxt);
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $order = $this->getMock('oxpsEasyCreditOrder', array('getDic'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($tilgungsplanTxt, $order->getTilgungsplanText());
    }

    public function testGetTilgungsplanTextEmpty()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $order = $this->getMock('oxpsEasyCreditOrder', array('getDic'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($order->getTilgungsplanText());
    }

    public function testGetUrlVorvertraglicheInformationen()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $url = 'https://test.url';
        $allgemeineVorgangsdaten = new stdClass();
        $allgemeineVorgangsdaten->urlVorvertraglicheInformationen = $url;
        $storage->setAllgemeineVorgangsdaten($allgemeineVorgangsdaten);
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $order = $this->getMock('oxpsEasyCreditOrder', array('getDic'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($url, $order->getUrlVorvertraglicheInformationen());
    }

    public function testGetUrlVorvertraglicheInformationenEmpty()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $order = $this->getMock('oxpsEasyCreditOrder', array('getDic'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($order->getUrlVorvertraglicheInformationen());
    }

    public function testGetPaymentPlanTxt()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $text = 'payment plan';
        $storage->setRatenplanTxt($text);
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $order = $this->getMock('oxpsEasyCreditOrder', array('getDic'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals($text, $order->getPaymentPlanTxt());
    }

    public function testGetPaymentPlanTxtEmpty()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $order = $this->getMock('oxpsEasyCreditOrder', array('getDic'));
        $order->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($order->getPaymentPlanTxt());
    }

    public function testGetPaymentPlanTxtEmptyStandardDic()
    {
        $order = oxNew('order');
        $this->assertNull($order->getPaymentPlanTxt());
    }
}