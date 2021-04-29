<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicConfig;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditPayment;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;
use OxidProfessionalServices\EasyCredit\Core\PayLoad\EasyCreditPayloadFactory;

/**
 * Class EasyCreditPaymentTest
 */
class EasyCreditPaymentTest extends UnitTestCase
{
    /**
     * Set up test environment
     *
     * @return null
     */
    public function setUp():void
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     * @return null
     */
    public function tearDown():void
    {
        parent::tearDown();
    }

    protected function buildDic($oxSession)
    {
        $mockOxConfig = $this->getMock('oxConfig', [], []);

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

    public function testGetDic()
    {
        $payment = oxNew(Payment::class);
        $this->assertNotNull($payment->getDic());
    }

    public function testGetBasket()
    {
        $payment = oxNew(Payment::class);
        $this->assertNotNull($payment->getBasket());
    }

    public function testIsEasyCreditPermittedNoFrontend()
    {
        $payment = oxNew(Payment::class);
        $this->assertTrue($payment->isEasyCreditPermitted());
    }

//    public function testIsEasyCreditPermittedFrontend()
//    {
//        $session = oxNew(EasyCreditSession::class);
//        $dic = $this->buildDic($session);
//
//        $dic->getConfig()->setConfigParam('oxpsECAquBorderConsiderFrontend', true);
//
//        $payment = $this->getMock(Payment::class, array('getDic'));
//        $payment->expects($this->any())->method('getDic')->willReturn($dic);
//
//        $this->assertTrue($payment->isEasyCreditPermitted());
//    }

    public function testIsEasyCreditPossible()
    {
        $payment = oxNew(Payment::class);
        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testIsEasyCreditPossibleNotPermitted()
    {
        $payment = $this->getMock(EasyCreditPayment::class, ['isEasyCreditPermitted']);
        $payment->expects($this->any())->method('isEasyCreditPermitted')->willReturn(false);

        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testIsEasyCreditPossibleAddressMismatch()
    {
        $payment = $this->getMock(EasyCreditPayment::class, ['isAddressMismatch']);
        $payment->expects($this->any())->method('isAddressMismatch')->willReturn(true);

        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testIsEasyCreditPossibleExampleCalculation()
    {
        $payment = $this->getMock(EasyCreditPayment::class, ['getExampleCalulation']);
        $payment->expects($this->any())->method('getExampleCalulation')->willReturn(false);

        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testGetExampleCalculationResponse()
    {
        $payment = $this->getMock(EasyCreditPayment::class, ['getPrice']);
        $payment->expects($this->any())->method('getPrice')->willReturn(false);

        $this->assertFalse($payment->getExampleCalculationResponse());
    }

    public function testGetExampleCalculationPrice()
    {
        $payment = oxNew(Payment::class);
        $this->assertNull($payment->getExampleCalculationPrice('dummy'));
    }

    public function testIsAddressMismatchWithDelAddress()
    {
        $delAddress = oxNew(Address::class);

        $payment = $this->getMock(EasyCreditPayment::class, ['getDelAddress']);
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);

        $this->assertTrue($payment->isAddressMismatch());
    }

    public function testIsAddressMismatchWithDelAddressAndUser()
    {
        $delAddress = oxNew(Address::class);
        $user = oxNew(User::class);

        $payment = $this->getMock(EasyCreditPayment::class, array('getDelAddress', 'getUser'));
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertFalse($payment->isAddressMismatch());
    }

    public function testIsForeignAddressWithDelAddress()
    {
        $delAddress = oxNew(Address::class);
        $user = oxNew(User::class);

        $payment = $this->getMock(EasyCreditPayment::class, ['getDelAddress', 'getUser']);
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertTrue($payment->isForeignAddress());
    }

    public function testIsForeignAddressWithoutDelAddress()
    {
        $user = oxNew(User::class);

        $payment = $this->getMock(EasyCreditPayment::class, ['getUser']);
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertTrue($payment->isForeignAddress());
    }

    public function testIsPackstationWithDelAddress()
    {
        $delAddress = oxNew(Address::class);

        $payment = $this->getMock(EasyCreditPayment::class, ['getDelAddress']);
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);

        $this->assertFalse($payment->isPackstation());
    }

    public function testIsPackstationWithDelUser()
    {
        $user = oxNew(User::class);

        $payment = $this->getMock(EasyCreditPayment::class, ['getUser']);
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertFalse($payment->isPackstation());
    }

    public function testValidatePayment()
    {
        $payment = oxNew(Payment::class);
        $this->assertNull($payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditNotPossible()
    {
        oxRegistry::getSession()->setVariable('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $payment = oxNew(Payment::class);
        $this->assertNull($payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditPossible()
    {
        oxRegistry::getSession()->setVariable('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $payment = $this->getMock(EasyCreditPayment::class, ['isEasyCreditPossible']);
        $payment->expects($this->any())->method('isEasyCreditPossible')->willReturn(true);

        $this->assertEquals('EasyCreditDispatcher?fnc=initializeandredirect', $payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditPossibleAddProfileDataException()
    {
        oxRegistry::getSession()->setVariable('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $payment = $this->getMock(EasyCreditPayment::class, ['isEasyCreditPossible', 'addProfileData']);
        $payment->expects($this->any())->method('isEasyCreditPossible')->willReturn(true);
        $payment->expects($this->any())->method('addProfileData')->willThrowException(new \Exception('TEST'));

        $this->assertNull($payment->validatePayment());
    }

    public function testAddProfileDataWithBirthDate()
    {
        $user = oxNew(User::class);

        $payment = $this->getMock(EasyCreditPayment::class, ['getValidatedDateOfBirth', 'getUser']);
        $payment->expects($this->any())->method('getValidatedDateOfBirth')->willReturn('1980-05-25');
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertNull($payment->addProfileData());
    }

    public function testAddProfileDataWithSalutation()
    {
        $user = oxNew(User::class);

        $payment = $this->getMock(EasyCreditPayment::class, ['getValidatedSalutation', 'getUser']);
        $payment->expects($this->any())->method('getValidatedSalutation')->willReturn('MR');
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertNull($payment->addProfileData());
    }

    public function testLoadAgreementTxt()
    {
        $response = new \stdClass();
        $response->zustimmungDatenuebertragungPaymentPage = 'dummy';

        $payment = $this->getMock(EasyCreditPayment::class, ['call']);
        $payment->expects($this->any())->method('call')->willReturn($response);

        $this->assertEquals('dummy', $payment->loadAgreementTxt());
    }

    public function testIsProfileDataMissing()
    {
        $payment = oxNew(Payment::class);
        $this->assertTrue($payment->isProfileDataMissing());
    }

    public function testHasSalutation()
    {
        $payment = oxNew(Payment::class);
        $this->assertFalse($payment->hasSalutation());
    }

    public function testGetValidatedDateOfBirth()
    {
        $requestData = [
            'oxuser__oxbirthdate' => [
                'year' => 2018,
                'month' => 6,
                'day' => 15,
            ]
        ];

        $user = oxNew(User::class);

        $payment = oxNew(Payment::class);
        $this->assertEquals('2018-06-15', $payment->getValidatedDateOfBirth($requestData, $user));
    }

    public function testGetValidatedDateOfBirthInFuture()
    {
        $this->expectExceptionMessage(OXPS_EASY_CREDIT_ERROR_DATEOFBIRTH_INVALID);
        $this->expectException(EasyCreditException::class);
        $requestData = array(
            'oxuser__oxbirthdate' => array(
                'year' => 2100,
                'month' => 1,
                'day' => 1,
            )
        );

        $user = oxNew(User::class);

        $payment = oxNew(Payment::class);
        $payment->getValidatedDateOfBirth($requestData, $user);
    }

    public function testGetValidatedSalutation()
    {
        $requestData = [
            'oxuser__oxsal' => "MR"
        ];

        $user = oxNew(User::class);

        $payment = oxNew(Payment::class);
        $this->assertEquals('MR', $payment->getValidatedSalutation($requestData));
    }
}