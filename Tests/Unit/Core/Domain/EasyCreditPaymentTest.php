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

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditPayment;

/**
 * Class EasyCreditOxPaymentTest
 */
class EasyCreditOxPaymentTest extends UnitTestCase
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

    public function testIsEasyCreditInstallmentTrue(): void
    {
        $payment = oxNew(Payment::class);
        $payment->setId(EasyCreditPayment::EASYCREDIT_PAYMENTID);
        $this->assertTrue($payment->isEasyCreditInstallment());
    }

    public function testIsEasyCreditInstallmentFalse(): void
    {
        $payment = oxNew(Payment::class);
        $payment->setId('something');
        $this->assertFalse($payment->isEasyCreditInstallment());
    }

    public function testIsEasyCreditInstallmentByIdTrue(): void
    {
        $this->assertTrue(EasyCreditPayment::isEasyCreditInstallmentById(EasyCreditPayment::EASYCREDIT_PAYMENTID));
    }

    public function testIsEasyCreditInstallmentByIdFalse(): void
    {
        $this->assertFalse(EasyCreditPayment::isEasyCreditInstallmentById('something'));
    }
}