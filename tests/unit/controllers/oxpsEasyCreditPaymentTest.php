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
 * Class oxpsEasyCreditPaymentTest
 */
class oxpsEasyCreditPaymentTest extends OxidTestCase
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

        $session = oxNew('oxpsEasyCreditDicSession', $oxSession);
        $mockApiConfig = oxNew('oxpsEasyCreditApiConfig', oxpsEasyCreditDicFactory::getApiConfigArray());
        $mockLogging = $this->getMock('oxpsEasyCreditLogging', array(), array(array()));
        $mockPayloadFactory = $this->getMock('oxpsEasyCreditPayloadFactory', array(), array());
        $mockDicConfig = $this->getMock('oxpsEasyCreditDicConfig', array(), array($mockOxConfig));

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

    public function testGetDic()
    {
        $payment = oxNew('payment');
        $this->assertNotNull($payment->getDic());
    }

    public function testGetBasket()
    {
        $payment = oxNew('payment');
        $this->assertNotNull($payment->getBasket());
    }

    public function testIsEasyCreditPermittedNoFrontend()
    {
        $payment = oxNew('payment');
        $this->assertTrue($payment->isEasyCreditPermitted());
    }

//    public function testIsEasyCreditPermittedFrontend()
//    {
//        $session = oxNew('oxpsEasyCreditOxSession');
//        $dic = $this->buildDic($session);
//
//        $dic->getConfig()->setConfigParam('oxpsECAquBorderConsiderFrontend', true);
//
//        $payment = $this->getMock('payment', array('getDic'));
//        $payment->expects($this->any())->method('getDic')->willReturn($dic);
//
//        $this->assertTrue($payment->isEasyCreditPermitted());
//    }

    public function testIsEasyCreditPossible()
    {
        $payment = oxNew('payment');
        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testIsEasyCreditPossibleNotPermitted()
    {
        $payment = $this->getMock('oxpsEasyCreditPayment', array('isEasyCreditPermitted'));
        $payment->expects($this->any())->method('isEasyCreditPermitted')->willReturn(false);

        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testIsEasyCreditPossibleAddressMismatch()
    {
        $payment = $this->getMock('oxpsEasyCreditPayment', array('isAddressMismatch'));
        $payment->expects($this->any())->method('isAddressMismatch')->willReturn(true);

        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testIsEasyCreditPossibleExampleCalculation()
    {
        $payment = $this->getMock('oxpsEasyCreditPayment', array('getExampleCalulation'));
        $payment->expects($this->any())->method('getExampleCalulation')->willReturn(false);

        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testGetExampleCalculationResponse()
    {
        $payment = $this->getMock('oxpsEasyCreditPayment', array('getPrice'));
        $payment->expects($this->any())->method('getPrice')->willReturn(false);

        $this->assertFalse($payment->getExampleCalculationResponse());
    }

    public function testGetExampleCalculationPrice()
    {
        $payment = oxNew('payment');
        $this->assertNull($payment->getExampleCalculationPrice('dummy'));
    }

    public function testIsAddressMismatchWithDelAddress()
    {
        $delAddress = oxNew('oxaddress');

        $payment = $this->getMock('oxpsEasyCreditPayment', array('getDelAddress'));
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);

        $this->assertTrue($payment->isAddressMismatch());
    }

    public function testIsAddressMismatchWithDelAddressAndUser()
    {
        $delAddress = oxNew('oxaddress');
        $user = oxNew('oxuser');

        $payment = $this->getMock('oxpsEasyCreditPayment', array('getDelAddress', 'getUser'));
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertFalse($payment->isAddressMismatch());
    }

    public function testIsForeignAddressWithDelAddress()
    {
        $delAddress = oxNew('oxaddress');
        $user = oxNew('oxuser');

        $payment = $this->getMock('oxpsEasyCreditPayment', array('getDelAddress', 'getUser'));
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertTrue($payment->isForeignAddress());
    }

    public function testIsForeignAddressWithoutDelAddress()
    {
        $user = oxNew('oxuser');

        $payment = $this->getMock('oxpsEasyCreditPayment', array('getUser'));
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertTrue($payment->isForeignAddress());
    }

    public function testIsPackstationWithDelAddress()
    {
        $delAddress = oxNew('oxaddress');

        $payment = $this->getMock('oxpsEasyCreditPayment', array('getDelAddress'));
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);

        $this->assertFalse($payment->isPackstation());
    }

    public function testIsPackstationWithDelUser()
    {
        $user = oxNew('oxuser');

        $payment = $this->getMock('oxpsEasyCreditPayment', array('getUser'));
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertFalse($payment->isPackstation());
    }

    public function testValidatePayment()
    {
        $payment = oxNew('payment');
        $this->assertNull($payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditNotPossible()
    {
        oxRegistry::getSession()->setVariable('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $payment = oxNew('payment');
        $this->assertNull($payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditPossible()
    {
        oxRegistry::getSession()->setVariable('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $payment = $this->getMock('oxpsEasyCreditPayment', array('isEasyCreditPossible'));
        $payment->expects($this->any())->method('isEasyCreditPossible')->willReturn(true);

        $this->assertEquals('oxpsEasyCreditDispatcher?fnc=initializeandredirect', $payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditPossibleAddProfileDataException()
    {
        oxRegistry::getSession()->setVariable('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $payment = $this->getMock('oxpsEasyCreditPayment', array('isEasyCreditPossible', 'addProfileData'));
        $payment->expects($this->any())->method('isEasyCreditPossible')->willReturn(true);
        $payment->expects($this->any())->method('addProfileData')->willThrowException(new Exception('TEST'));

        $this->assertNull($payment->validatePayment());
    }

    public function testAddProfileDataWithBirthDate()
    {
        $user = oxNew('oxuser');

        $payment = $this->getMock('oxpsEasyCreditPayment', array('getValidatedDateOfBirth', 'getUser'));
        $payment->expects($this->any())->method('getValidatedDateOfBirth')->willReturn('1980-05-25');
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertNull($payment->addProfileData());
    }

    public function testAddProfileDataWithSalutation()
    {
        $user = oxNew('oxuser');

        $payment = $this->getMock('oxpsEasyCreditPayment', array('getValidatedSalutation', 'getUser'));
        $payment->expects($this->any())->method('getValidatedSalutation')->willReturn('MR');
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertNull($payment->addProfileData());
    }

    public function testLoadAgreementTxt()
    {
        $response = new stdClass();
        $response->zustimmungDatenuebertragungPaymentPage = 'dummy';

        $payment = $this->getMock('oxpsEasyCreditPayment', array('call'));
        $payment->expects($this->any())->method('call')->willReturn($response);

        $this->assertEquals('dummy', $payment->loadAgreementTxt());
    }

    public function testIsProfileDataMissing()
    {
        $payment = oxNew('payment');
        $this->assertTrue($payment->isProfileDataMissing());
    }

    public function testHasSalutation()
    {
        $payment = oxNew('payment');
        $this->assertFalse($payment->hasSalutation());
    }

    public function testGetValidatedDateOfBirth()
    {
        $requestData = array(
            'oxuser__oxbirthdate' => array(
                'year' => 2018,
                'month' => 6,
                'day' => 15,
            )
        );

        $user = oxNew('oxuser');

        $payment = oxNew('payment');
        $this->assertEquals('2018-06-15', $payment->getValidatedDateOfBirth($requestData, $user));
    }

    /**
     * @expectedException oxpsEasyCreditException
     * @expectedExceptionMessage OXPS_EASY_CREDIT_ERROR_DATEOFBIRTH_INVALID
     */
    public function testGetValidatedDateOfBirthInFuture()
    {
        $requestData = array(
            'oxuser__oxbirthdate' => array(
                'year' => 2100,
                'month' => 1,
                'day' => 1,
            )
        );

        $user = oxNew('oxuser');

        $payment = oxNew('payment');
        $payment->getValidatedDateOfBirth($requestData, $user);
    }

    public function testGetValidatedSalutation()
    {
        $requestData = array(
            'oxuser__oxsal' => "MR"
        );

        $user = oxNew('oxuser');

        $payment = oxNew('payment');
        $this->assertEquals('MR', $payment->getValidatedSalutation($requestData));
    }
}