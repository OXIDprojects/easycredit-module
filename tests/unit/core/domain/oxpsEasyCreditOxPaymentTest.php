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
 * Class oxpsEasyCreditOxPaymentTest
 */
class oxpsEasyCreditOxPaymentTest extends OxidTestCase
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

    public function testIsEasyCreditInstallmentTrue()
    {
        $payment = oxNew('oxpayment');
        $payment->setId(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);
        $this->assertTrue($payment->isEasyCreditInstallment());
    }

    public function testIsEasyCreditInstallmentFalse()
    {
        $payment = oxNew('oxpayment');
        $payment->setId('something');
        $this->assertFalse($payment->isEasyCreditInstallment());
    }

    public function testIsEasyCreditInstallmentByIdTrue()
    {
        $this->assertTrue(oxpsEasyCreditOxPayment::isEasyCreditInstallmentById(oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID));
    }

    public function testIsEasyCreditInstallmentByIdFalse()
    {
        $this->assertFalse(oxpsEasyCreditOxPayment::isEasyCreditInstallmentById('something'));
    }

    public function testGetEasyCreditAquisitionBorderValue()
    {
        $borderValue = 1000.0;

        $payment = oxNew('oxpayment');
        $payment->getConfig()->setConfigParam('oxpsECAquisitionBorderValue', $borderValue);

        $this->assertEquals($borderValue, $payment->getEasyCreditAquisitionBorderValue());
    }

    public function testGetFEasyCreditAquisitionBorderValue()
    {
        $payment = oxNew('oxpayment');
        $payment->getConfig()->setConfigParam('oxpsECAquisitionBorderValue', 750.14);

        $this->assertEquals('750,14 EUR', $payment->getFEasyCreditAquisitionBorderValue());
    }

    public function testGetFEasyCreditAquisitionBorderValueNoValue()
    {
        $payment = oxNew('oxpayment');

        $this->assertNull($payment->getFEasyCreditAquisitionBorderValue());
    }

    public function testGetFEasyCreditAquisitionBorderLastUpdate()
    {
        $t = time();

        $payment = oxNew('oxpayment');
        $payment->getConfig()->setConfigParam('oxpsECAquisitionBorderLastUpdate', $t);

        $this->assertEquals($t, $payment->getEasyCreditAquisitionBorderLastUpdate());
    }

    public function testGetEasyCreditAquisitionBorderLastUpdate()
    {
        $t = "31 July 2018";

        $payment = oxNew('oxpayment');
        $payment->getConfig()->setConfigParam('oxpsECAquisitionBorderLastUpdate', $t);

        $this->assertEquals('31.07.2018 00:00', $payment->getFEasyCreditAquisitionBorderLastUpdate());
    }

    public function testGetEasyCreditAquisitionBorderLastUpdateNoTime()
    {

        $payment = oxNew('oxpayment');
        $payment->getConfig()->setConfigParam('oxpsECAquisitionBorderLastUpdate', null);
        $this->assertNull($payment->getFEasyCreditAquisitionBorderLastUpdate());
    }
}