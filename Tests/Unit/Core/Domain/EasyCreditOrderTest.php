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

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Core\Domain;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicSession;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditBasket;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditOrder;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditPayment;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditSession;
use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditInitializeRequestBuilder;
use OxidProfessionalServices\EasyCredit\Core\PayLoad\EasyCreditPayloadFactory;

/**
 * Class EasyCreditOxOrderTest
 */
class EasyCreditOxOrderTest extends UnitTestCase
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

    public function testFinalizeOrderInvalidPayment(): void
    {
        $oxBasket = oxNew(Basket::class);

        $oxUser = oxNew(User::class);

        $oxOrder = oxNew(Order::class);
        $this->assertEquals(oxOrder::ORDER_STATE_INVALIDPAYMENT, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testFinalizeOrderNoStorage(): void
    {
        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getPaymentId']);
        $oxBasket->expects($this->any())->method('getPaymentId')->willReturn(EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxUser = oxNew(User::class);

        $oxOrder = oxNew(Order::class);
        $this->assertFalse($oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testFinalizeOrderWithStorageNotInitializing(): void
    {
        $dic = $this->getMockedDic();

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);
        $dicSession->setStorage(
            oxNew(
                EasyCreditStorage::class,
                'tbVorgangskennung',
                'fachlicheVorgangskennung',
                'authorizationHash',
                1000.0
            )
        );

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $oxUser = oxNew(User::class);

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testFinalizeOrderWithEmptyStorage(): void
    {
        $dic = $this->getMockedDic();

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);
        $dicSession->setStorage(null);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = oxNew(User::class);

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    public function testFinalizeOrderWithStorageInitialized(): void
    {
        $dic = $this->getMockedDic();

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = oxNew(User::class);

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validatePayment']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validatePayment')->willReturn(Order::ORDER_STATE_INVALIDPAYMENT);

        $this->assertEquals(Order::ORDER_STATE_INVALIDPAYMENT, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    public function testFinalizeOrderStateOkWithConfirmException(): void
    {
        $dic = $this->getMockedDic();

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    public function testFinalizeOrderStateOk(): void
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);


        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    public function testFinalizeOrderIsConfirmedNoValidate(): void
    {
        Registry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', false);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    public function testFinalizeOrderIsConfirmedNoResponse(): void
    {
        Registry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', true);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn(null);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = Registry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertIsArray($defaultErrors);
        $this->assertCount(2, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals(Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED'),
                            $errorMessages[0]);
        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[1]);
    }

    public function testFinalizeOrderIsConfirmedNoWsMessages(): void
    {
        Registry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', true);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new \stdClass();
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = Registry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertIsArray($defaultErrors);
        $this->assertCount(2, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals(Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED'),
                            $errorMessages[0]);
        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[1]);
    }

    public function testFinalizeOrderIsConfirmedNoMessages(): void
    {
        Registry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', true);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response             = new \stdClass();
        $wsMessages           = new \stdClass();
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = Registry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertIsArray($defaultErrors);
        $this->assertCount(2, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals(Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED'),
                            $errorMessages[0]);
        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[1]);
    }

    public function testFinalizeOrderIsConfirmedNoFirstMessage(): void
    {
        Registry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', true);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response             = new \stdClass();
        $wsMessages           = new \stdClass();
        $messages             = ['xyz'];
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = Registry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertIsArray($defaultErrors);
        $this->assertCount(2, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals(Registry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED'),
                            $errorMessages[0]);
        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[1]);
    }

    public function testFinalizeOrderIsConfirmedFirstMessageConfirmed(): void
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response             = new \stdClass();
        $wsMessages           = new \stdClass();
        $firstMessage         = new \stdClass();
        $firstMessage->key    = EasyCreditOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages             = [$firstMessage];
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = Registry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertIsArray($defaultErrors);
        $this->assertCount(1, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[0]);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutStorageVorgangskennung(): void
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            EasyCreditStorage::class,
            null,
            'fachlicheVorgangskennung',
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response             = new \stdClass();
        $wsMessages           = new \stdClass();
        $firstMessage         = new \stdClass();
        $firstMessage->key    = EasyCreditOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages             = [$firstMessage];
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutFachlicheVorgangskennung(): void
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            null,
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response             = new \stdClass();
        $wsMessages           = new \stdClass();
        $firstMessage         = new \stdClass();
        $firstMessage->key    = EasyCreditOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages             = [$firstMessage];
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutAllgemeineVorgangsdaten(): void
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten(null);
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response             = new \stdClass();
        $wsMessages           = new \stdClass();
        $firstMessage         = new \stdClass();
        $firstMessage->key    = EasyCreditOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages             = [$firstMessage];
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutTilgungsplanTxt(): void
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt(null);
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response             = new \stdClass();
        $wsMessages           = new \stdClass();
        $firstMessage         = new \stdClass();
        $firstMessage->key    = EasyCreditOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages             = [$firstMessage];
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutRatenplanTxt(): void
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt(null);
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew(Price::class);
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response             = new \stdClass();
        $wsMessages           = new \stdClass();
        $firstMessage         = new \stdClass();
        $firstMessage->key    = EasyCreditOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages             = [$firstMessage];
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    public function testTilgunsplanTxtNoStorage(): void
    {
        $dic = $this->getMockedDic(true);

        $oxOrder = $this->getMock(EasyCreditOrder::class, ['getDic', 'validateOrder', 'getConfirmResponse']);
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($oxOrder->getTilgungsplanTxt());
    }

    public function testgetFInterestsValueWithInterestValue(): void
    {
        $oxOrder                               = oxNew(Order::class);
        $oxOrder->oxorder__ecredinterestsvalue = new Field(2.5);
        $this->assertNotNull($oxOrder->getFInterestsValue());
        $this->assertEquals('2,50', $oxOrder->getFInterestsValue());
    }

    private function getMockedDic($apiConfigured = false)
    {
        return oxNew(
            EasyCreditDic::class,
            oxNew(EasyCreditDicSession::class, oxNew(EasyCreditSession::class)),
            oxNew(EasyCreditApiConfig::class, $apiConfigured ? EasyCreditDicFactory::getApiConfigArray() : []),
            oxNew(EasyCreditPayloadFactory::class),
            oxNew(EasyCreditLogging::class, []),
            oxNew(EasyCreditDicConfig::class, Registry::getConfig())
        );
    }

    private function buildUser(): User
    {
        $user = oxNew(User::class);

        $user->oxuser__oxid = new Field('1234');

        // bill address
        $user->oxuser__oxcompany     = new Field('oxcompany');
        $user->oxuser__oxusername    = new Field('test@test.net');
        $user->oxuser__oxfname       = new Field('oxfname');
        $user->oxuser__oxlname       = new Field('oxlname');
        $user->oxuser__oxstreet      = new Field('oxstreet');
        $user->oxuser__oxstreetnr    = new Field('oxstreetnr');
        $user->oxuser__oxaddinfo     = new Field('oxaddinfo');
        $user->oxuser__oxustid       = new Field('oxustid');
        $user->oxuser__oxcity        = new Field('oxcity');
        $user->oxuser__oxcountryid   = new Field('oxcountryid');
        $user->oxuser__oxstateid     = new Field('oxstateid');
        $user->oxuser__oxzip         = new Field('oxzip');
        $user->oxuser__oxfon         = new Field('oxfon');
        $user->oxuser__oxfax         = new Field('oxfax');
        $user->oxuser__oxsal         = new Field('oxsal');
        $user->oxuser__oxustidstatus = new Field('oxustidstatus');

        return $user;
    }

    protected function getPaymentHash($oxUser, $oxBasket, $dic)
    {
        return md5(json_encode($this->getCurrentInitializationData($oxUser, $oxBasket, $dic)));
    }

    protected function getCurrentInitializationData($oUser, $oBasket, $dic)
    {
        $requestBuilder = oxNew(EasyCreditInitializeRequestBuilder::class);

        $requestBuilder->setUser($oUser);
        $requestBuilder->setBasket($oBasket);
        $requestBuilder->setShippingAddress($this->getShippingAddress());

        $shopEdition = EasyCreditHelper::getShopSystem($this->getConfig()->getActiveShop());
        $requestBuilder->setShopEdition($shopEdition);

        $moduleVersion = EasyCreditHelper::getModuleVersion($dic);
        $requestBuilder->setModuleVersion($moduleVersion);

        $requestBuilder->setBaseLanguage(Registry::getLang()->getBaseLanguage());

        $data = $requestBuilder->getInitializationData();
        return $data;
    }

    protected function getShippingAddress(): Address
    {
        $oOrder = oxNew(Order::class);
        return $oOrder->getDelAddressInfo();
    }
}