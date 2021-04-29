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
 * Class oxpsEasyCreditOxOrderTest
 */
class oxpsEasyCreditOxOrderTest extends OxidTestCase
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

    public function testFinalizeOrderInvalidPayment()
    {
        $oxBasket = oxNew('oxbasket');

        $oxUser = oxNew('oxuser');

        $oxOrder = oxNew('oxorder');
        $this->assertEquals(oxOrder::ORDER_STATE_INVALIDPAYMENT, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testFinalizeOrderNoStorage()
    {
        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getPaymentId'));
        $oxBasket->expects($this->any())->method('getPaymentId')->willReturn(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxUser = oxNew('oxuser');

        $oxOrder = oxNew('oxorder');
        $this->assertFalse($oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testFinalizeOrderWithStorageNotInitializing()
    {
        $dic = $this->getMockedDic();

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);
        $dicSession->setStorage(
            oxNew(
                'EasyCreditStorage',
                'tbVorgangskennung',
                'fachlicheVorgangskennung',
                'authorizationHash',
                1000.0
            )
        );

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $oxUser = oxNew('oxuser');

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testFinalizeOrderWithEmptyStorage()
    {
        $dic = $this->getMockedDic();

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);
        $dicSession->setStorage(null);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = oxNew('oxuser');

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    public function testFinalizeOrderWithStorageInitialized()
    {
        $dic = $this->getMockedDic();

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = oxNew('oxuser');

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validatePayment'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validatePayment')->willReturn(oxOrder::ORDER_STATE_INVALIDPAYMENT);

        $this->assertEquals(oxOrder::ORDER_STATE_INVALIDPAYMENT, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    public function testFinalizeOrderStateOkWithConfirmException()
    {
        $dic = $this->getMockedDic();

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    public function testFinalizeOrderStateOk()
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);


        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    public function testFinalizeOrderIsConfirmedNoValidate()
    {
        oxRegistry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', false);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));
    }

    public function testFinalizeOrderIsConfirmedNoResponse()
    {
        oxRegistry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', true);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn(null);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = oxRegistry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertTrue(is_array($errors));
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertTrue(is_array($defaultErrors));
        $this->assertCount(2, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals(oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED'), $errorMessages[0]);
        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[1]);
    }

    public function testFinalizeOrderIsConfirmedNoWsMessages()
    {
        oxRegistry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', true);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = oxRegistry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertTrue(is_array($errors));
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertTrue(is_array($defaultErrors));
        $this->assertCount(2, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals(oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED'), $errorMessages[0]);
        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[1]);
    }

    public function testFinalizeOrderIsConfirmedNoMessages()
    {
        oxRegistry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', true);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);


        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $wsMessages = new stdClass();
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = oxRegistry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertTrue(is_array($errors));
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertTrue(is_array($defaultErrors));
        $this->assertCount(2, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals(oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED'), $errorMessages[0]);
        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[1]);
    }

    public function testFinalizeOrderIsConfirmedNoFirstMessage()
    {
        oxRegistry::getConfig()->setConfigParam('oxpsECCheckoutValidConfirm', true);

        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $wsMessages = new stdClass();
        $messages = array('xyz');
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = oxRegistry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertTrue(is_array($errors));
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertTrue(is_array($defaultErrors));
        $this->assertCount(2, $defaultErrors);

        $errorMessages = array_map(function ($error) {
            return unserialize($error)->getOxMessage();
        }, $defaultErrors);

        $this->assertEquals(oxRegistry::getLang()->translateString('OXPS_EASY_CREDIT_ERROR_BESTAETIGEN_FAILED'), $errorMessages[0]);
        $this->assertEquals('Es ist ein Fehler aufgetreten.', $errorMessages[1]);
    }

    public function testFinalizeOrderIsConfirmedFirstMessageConfirmed()
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $paymentHash = $this->getPaymentHash($oxUser, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $wsMessages = new stdClass();
        $firstMessage = new stdClass();
        $firstMessage->key = oxpsEasyCreditOxOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages = array($firstMessage);
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $this->assertEquals(oxorder::ORDER_STATE_OK, $oxOrder->finalizeOrder($oxBasket, $oxUser));

        $errors = oxRegistry::getSession()->getVariable('Errors');
        $this->assertNotNull($errors);
        $this->assertTrue(is_array($errors));
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors['default']));

        $defaultErrors = $errors['default'];
        $this->assertTrue(is_array($defaultErrors));
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
    public function testFinalizeOrderWithoutStorageVorgangskennung()
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            'EasyCreditStorage',
            null,
            'fachlicheVorgangskennung',
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $wsMessages = new stdClass();
        $firstMessage = new stdClass();
        $firstMessage->key = oxpsEasyCreditOxOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages = array($firstMessage);
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutFachlicheVorgangskennung()
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            null,
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $wsMessages = new stdClass();
        $firstMessage = new stdClass();
        $firstMessage->key = oxpsEasyCreditOxOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages = array($firstMessage);
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutAllgemeineVorgangsdaten()
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten(null);
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $wsMessages = new stdClass();
        $firstMessage = new stdClass();
        $firstMessage->key = oxpsEasyCreditOxOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages = array($firstMessage);
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutTilgungsplanTxt()
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt(null);
        $storage->setRatenplanTxt('paymentPlanTxt');
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $wsMessages = new stdClass();
        $firstMessage = new stdClass();
        $firstMessage->key = oxpsEasyCreditOxOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages = array($firstMessage);
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @throws oxSystemComponentException
     */
    public function testFinalizeOrderWithoutRatenplanTxt()
    {
        $dic = $this->getMockedDic(true);

        $dicSession = $dic->getSession();
        $dicSession->set('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            '3faa449deae074523887722643ca8796',
            1000.0
        );
        $storage->setAllgemeineVorgangsdaten('allgemeineVorgangsdaten');
        $storage->setTilgungsplanTxt('tilgungsplanText');
        $storage->setRatenplanTxt(null);
        $dicSession->setStorage($storage);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(1000.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $oxUser = $this->buildUser();

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);
        $oxOrder->expects($this->any())->method('validateOrder')->willReturn(false);

        $response = new stdClass();
        $wsMessages = new stdClass();
        $firstMessage = new stdClass();
        $firstMessage->key = oxpsEasyCreditOxOrder::EASYCREDIT_BESTELLUNG_BESTAETIGT;
        $messages = array($firstMessage);
        $wsMessages->messages = $messages;
        $response->wsMessages = $wsMessages;
        $oxOrder->expects($this->any())->method('getConfirmResponse')->willReturn($response);

        $oxOrder->finalizeOrder($oxBasket, $oxUser);
    }

    public function testTilgunsplanTxtNoStorage()
    {
        $dic = $this->getMockedDic(true);

        $oxOrder = $this->getMock('oxpsEasyCreditOxOrder', array('getDic', 'validateOrder', 'getConfirmResponse'));
        $oxOrder->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($oxOrder->getTilgungsplanTxt());
    }

    public function testgetFInterestsValueWithInterestValue()
    {
        $oxOrder = oxNew('oxorder');
        $oxOrder->oxorder__ecredinterestsvalue = new oxField(2.5);
        $this->assertNotNull($oxOrder->getFInterestsValue());
        $this->assertEquals('2,50', $oxOrder->getFInterestsValue());
    }

    private function getMockedDic($apiConfigured = false)
    {
        return oxNew(
'EasyCreditDic',
           oxNew(EasyCreditDicSession::class, oxNew('oxpsEasyCreditOxSession')),
           oxNew(EasyCreditApiConfig::class, $apiConfigured ? oxpsEasyCreditDicFactory::getApiConfigArray() : array()),
            oxNew('EasyCreditPayloadFactory'),
            oxNew('EasyCreditLogging', array()),
            oxNew('EasyCreditDicConfig', oxRegistry::getConfig())
        );
    }

    private function buildUser()
    {
        $user = oxNew('oxuser');

        $user->oxuser__oxid = new oxField('1234');

        // bill address
        $user->oxuser__oxcompany = new oxField('oxcompany');
        $user->oxuser__oxusername = new oxField('test@test.net');
        $user->oxuser__oxfname = new oxField('oxfname');
        $user->oxuser__oxlname = new oxField('oxlname');
        $user->oxuser__oxstreet = new oxField('oxstreet');
        $user->oxuser__oxstreetnr = new oxField('oxstreetnr');
        $user->oxuser__oxaddinfo = new oxField('oxaddinfo');
        $user->oxuser__oxustid = new oxField('oxustid');
        $user->oxuser__oxcity = new oxField('oxcity');
        $user->oxuser__oxcountryid = new oxField('oxcountryid');
        $user->oxuser__oxstateid = new oxField('oxstateid');
        $user->oxuser__oxzip = new oxField('oxzip');
        $user->oxuser__oxfon = new oxField('oxfon');
        $user->oxuser__oxfax = new oxField('oxfax');
        $user->oxuser__oxsal = new oxField('oxsal');
        $user->oxuser__oxustidstatus = new oxField('oxustidstatus');

        return $user;
    }

    protected function getPaymentHash($oxUser, $oxBasket, $dic)
    {
        return md5(json_encode($this->getCurrentInitializationData($oxUser, $oxBasket, $dic)));
    }

    protected function getCurrentInitializationData($oUser, $oBasket, $dic)
    {
        $requestBuilder = oxNew('EasyCreditInitializeRequestBuilder');

        $requestBuilder->setUser($oUser);
        $requestBuilder->setBasket($oBasket);
        $requestBuilder->setShippingAddress($this->getShippingAddress());

        $shopEdition = oxpsEasyCreditHelper::getShopSystem($this->getConfig()->getActiveShop());
        $requestBuilder->setShopEdition($shopEdition);

        $moduleVersion = oxpsEasyCreditHelper::getModuleVersion($dic);
        $requestBuilder->setModuleVersion($moduleVersion);

        $requestBuilder->setBaseLanguage(oxRegistry::getLang()->getBaseLanguage());

        $data = $requestBuilder->getInitializationData();
        return $data;
    }

    protected function getShippingAddress()
    {
        /** @var $oOrder oxOrder */
        $oOrder = oxNew('oxorder');
        return $oOrder->getDelAddressInfo();
    }
}