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

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditBasket;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditPayment;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditSession;
use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;

/**
 * Class EasyCreditOxBasketTest
 */
class EasyCreditOxBasketTest extends UnitTestCase
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

    public function testHasInterestsAmount(): void
    {
        $oxBasket = oxNew(Basket::class);
        $this->assertNotTrue($oxBasket->hasInterestsAmount());
    }

    public function testGetInterestsAmount(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $session->setVariable('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $dic = $this->getMock(
            EasyCreditDic::class,
            array('getSession'),
            array(null, null, null, null, null)
        );
        $dic->expects($this->any())->method('getSession')->willReturn($session);

        $storage = oxNew(EasyCreditStorage::class, "TEST", "TEST", "TEST", 500.0);
        $storage->setInterestAmount(20.7);
        $dic->getSession()->setStorage($storage);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals(20.7, $oxBasket->getInterestsAmount());
    }

    public function testGetInterestsAmountNoStorage(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $session->setVariable('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $dic = $this->getMock(
            EasyCreditDic::class,
            array('getSession'),
            array(null, null, null, null, null)
        );
        $dic->expects($this->any())->method('getSession')->willReturn($session);

        $oxBasket = $this->getMock(EasyCreditBasket::class, array('getDic'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($oxBasket->getInterestsAmount());
    }

    public function testSetCostExclude(): void
    {
        $costName = 'oxpayment';

        $price = oxNew(Price::class);
        $price->setPrice(10.4);

        $oxBasket = oxNew(Basket::class);
        $oxBasket->setExcludeInstalmentsCosts(true);
        $oxBasket->setCost($costName, $price);

        $costs = $oxBasket->getCosts();
        $this->assertNotNull($costs);
        $this->assertTrue(is_array($costs));
        $this->assertTrue(isset($costs[$costName]));

        $p = $costs[$costName];
        $this->assertEquals(10.4, $p->getPrice());
    }

    public function testSetCostInclude(): void
    {
        $costName = 'oxpayment';

        $price = oxNew(Price::class);
        $price->setPrice(10.4);

        $oxBasket = oxNew(Basket::class);
        $oxBasket->setCost($costName, $price);

        $costs = $oxBasket->getCosts();
        $this->assertNotNull($costs);
        $this->assertTrue(is_array($costs));
        $this->assertTrue(isset($costs[$costName]));

        $p = $costs[$costName];
        $this->assertEquals(10.4, $p->getPrice());
    }

    public function testCalcInterestsCost(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $session->setVariable('paymentid', EasyCreditPayment::EASYCREDIT_PAYMENTID);

        $dic = $this->getMock(
            EasyCreditDic::class,
            array('getSession'),
            array(null, null, null, null, null)
        );
        $dic->expects($this->any())->method('getSession')->willReturn($session);

        $storage = oxNew(EasyCreditStorage::class, "TEST", "TEST", "TEST", 500.0);
        $storage->setInterestAmount(20.7);
        $dic->getSession()->setStorage($storage);

        $oxBasket = $this->getMock(EasyCreditBasket::class, array('getDic'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $interestCost = $oxBasket->calcInterestsCost();
        $this->assertNotNull($interestCost);
        $this->assertEquals(20.7, $interestCost->getPrice());
    }

    public function testCalculateBasket(): void
    {
        // calling calculateBasket to test _calcTotalPrice
        $oxBasket = oxNew(Basket::class);
        $oxBasket->calculateBasket();
    }
}
