<?php

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Controller;

use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Config;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Controller\EasyCreditPaymentController;
use OxidSolutionCatalysts\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDic;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicSession;
use OxidSolutionCatalysts\EasyCredit\Core\Exception\EasyCreditException;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Class EasyCreditPaymentTest
 */
class EasyCreditPaymentTest extends TestCase
{
    /**
     * Set up test environment
     *
     */
    public function setUp():void
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     */
    public function tearDown():void
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

    public function testGetDic()
    {
        $payment = oxNew(PaymentController::class);
        $this->assertNotNull($payment->getDic());
    }

    public function testGetBasket()
    {
        $payment = oxNew(PaymentController::class);
        $this->assertNotNull($payment->getBasket());
    }

    public function testIsEasyCreditPossible()
    {
        $payment = oxNew(PaymentController::class);
        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testIsEasyCreditPossibleAddressMismatch()
    {
        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAddressMismatch'])
            ->getMock();
        $payment->expects($this->any())->method('isAddressMismatch')->willReturn(true);

        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testIsEasyCreditPossibleExampleCalculation()
    {
        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExampleCalulation'])
            ->getMock();
        $payment->expects($this->any())->method('getExampleCalulation')->willReturn(false);

        $this->assertFalse($payment->isEasyCreditPossible());
    }

    public function testGetExampleCalculationResponse()
    {
        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $payment->expects($this->any())->method('getPrice')->willReturn(false);

        $this->assertFalse($payment->getExampleCalculationResponse());
    }

    public function testGetExampleCalculationPrice()
    {
        $payment = oxNew(PaymentController::class);
        $this->assertNull($payment->getExampleCalculationPrice('dummy'));
    }

    public function testIsAddressMismatchWithDelAddress()
    {
        $delAddress = oxNew(Address::class);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDelAddress', 'getUser'])
            ->getMock();
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);
        $payment->expects($this->any())->method('getUser')->willReturn(null);

        $this->assertTrue($payment->isAddressMismatch());
    }

    public function testIsAddressMismatchWithDelAddressAndUser()
    {
        $delAddress = oxNew(Address::class);
        $user = oxNew(User::class);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDelAddress',
                'getUser',
            ])
            ->getMock();
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertFalse($payment->isAddressMismatch());
    }

    public function testIsForeignAddressWithDelAddress()
    {
        $delAddress = oxNew(Address::class);
        $user = oxNew(User::class);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDelAddress',
                'getUser',
            ])
            ->getMock();
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertTrue($payment->isForeignAddress());
    }

    public function testIsForeignAddressWithoutDelAddress()
    {
        $user = oxNew(User::class);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertTrue($payment->isForeignAddress());
    }

    public function testIsPackstationWithDelAddress()
    {
        $delAddress = oxNew(Address::class);

        $payment =         $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDelAddress'])
            ->getMock();
        $payment->expects($this->any())->method('getDelAddress')->willReturn($delAddress);

        $this->assertFalse($payment->isPackstation());
    }

    public function testIsPackstationWithDelUser()
    {
        $user = oxNew(User::class);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertFalse($payment->isPackstation());
    }

    public function testValidatePayment()
    {
        $payment = oxNew(PaymentController::class);
        $this->assertNull($payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditNotPossible()
    {
        Registry::getSession()->setVariable('paymentid', EasyCreditHelper::EASYCREDIT_PAYMENTID);

        $payment = oxNew(PaymentController::class);
        $this->assertNull($payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditPossible()
    {
        Registry::getSession()->setVariable('paymentid', EasyCreditHelper::EASYCREDIT_PAYMENTID);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEasyCreditPossible'])
            ->getMock();
        $payment->expects($this->any())->method('isEasyCreditPossible')->willReturn(true);

        $this->assertEquals('EasyCreditDispatcher?fnc=initializeandredirect', $payment->validatePayment());
    }

    public function testValidatePaymentEasyCreditPossibleAddProfileDataException()
    {
        Registry::getSession()->setVariable('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isEasyCreditPossible',
                'addProfileData',
            ])
            ->getMock();
        $payment->expects($this->any())->method('isEasyCreditPossible')->willReturn(true);
        $payment->expects($this->any())->method('addProfileData')->willThrowException(new \Exception('TEST'));

        $this->assertNull($payment->validatePayment());
    }

    public function testAddProfileDataWithBirthDate()
    {
        $user = oxNew(User::class);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getValidatedDateOfBirth',
                'getUser',
            ])
            ->getMock();
        $payment->expects($this->any())->method('getValidatedDateOfBirth')->willReturn('1980-05-25');
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertNull($payment->addProfileData());
    }

    public function testAddProfileDataWithSalutation()
    {
        $user = oxNew(User::class);

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getValidatedSalutation',
                'getUser',
            ])
            ->getMock();
        $payment->expects($this->any())->method('getValidatedSalutation')->willReturn('MR');
        $payment->expects($this->any())->method('getUser')->willReturn($user);

        $this->assertNull($payment->addProfileData());
    }

    public function testLoadAgreementTxt()
    {
        $response = new \stdClass();
        $response->zustimmungDatenuebertragungPaymentPage = 'dummy';

        $payment = $this->getMockBuilder(EasyCreditPaymentController::class)
            ->disableOriginalConstructor()
            ->setMethods(['call'])
            ->getMock();
        $payment->expects($this->any())->method('call')->willReturn($response);

        $this->assertEquals('dummy', $payment->loadAgreementTxt());
    }

    public function testIsProfileDataMissing()
    {
        $payment = oxNew(PaymentController::class);
        $this->assertTrue($payment->isProfileDataMissing());
    }

    public function testHasSalutation()
    {
        $payment = oxNew(PaymentController::class);
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

        $payment = oxNew(PaymentController::class);
        $this->assertEquals('2018-06-15', $payment->getValidatedDateOfBirth($requestData, $user));
    }

    public function testGetValidatedDateOfBirthInFuture()
    {
        $this->expectExceptionMessage(Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_DATEOFBIRTH_INVALID'));
        $this->expectException(EasyCreditException::class);
        $requestData = [
            'oxuser__oxbirthdate' => [
                'year' => 2100,
                'month' => 1,
                'day' => 1,
            ]
        ];

        $user = oxNew(User::class);

        $payment = oxNew(PaymentController::class);
        $payment->getValidatedDateOfBirth($requestData, $user);
    }

    public function testGetValidatedSalutation()
    {
        $requestData = [
            'oxuser__oxsal' => "MR"
        ];

        $user = oxNew(User::class);

        $payment = oxNew(PaymentController::class);
        $this->assertEquals('MR', $payment->getValidatedSalutation($requestData));
    }
}