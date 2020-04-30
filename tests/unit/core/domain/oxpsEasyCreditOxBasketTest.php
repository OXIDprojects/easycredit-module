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
 * Class oxpsEasyCreditOxBasketTest
 */
class oxpsEasyCreditOxBasketTest extends OxidTestCase
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

    public function testHasInterestsAmount()
    {
        $oxBasket = oxNew('oxbasket');
        $this->assertNotTrue($oxBasket->hasInterestsAmount());
    }

    public function testGetInterestsAmount()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $session->setVariable('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $dic = $this->getMock(
            'oxpsEasyCreditDic',
            array('getSession'),
            array(null, null, null, null, null)
        );
        $dic->expects($this->any())->method('getSession')->willReturn($session);

        /** @var oxpsEasyCreditStorage $storage */
        $storage = oxNew('oxpsEasyCreditStorage', "TEST", "TEST", "TEST", 500.0);
        $storage->setInterestAmount(20.7);
        $dic->getSession()->setStorage($storage);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals(20.7, $oxBasket->getInterestsAmount());
    }

    public function testGetInterestsAmountNoStorage()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $session->setVariable('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $dic = $this->getMock(
            'oxpsEasyCreditDic',
            array('getSession'),
            array(null, null, null, null, null)
        );
        $dic->expects($this->any())->method('getSession')->willReturn($session);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertNull($oxBasket->getInterestsAmount());
    }

    public function testSetCostExclude()
    {
        $costName = 'oxpayment';

        /** @var oxPrice $price */
        $price = oxNew('oxPrice');
        $price->setPrice(10.4);

        $oxBasket = oxNew('oxbasket');
        $oxBasket->setExcludeInstalmentsCosts(true);
        $oxBasket->setCost($costName, $price);

        $costs = $oxBasket->getCosts();
        $this->assertNotNull($costs);
        $this->assertTrue(is_array($costs));
        $this->assertTrue(isset($costs[$costName]));

        $p = $costs[$costName];
        $this->assertEquals(10.4, $p->getPrice());
    }

    public function testSetCostInclude()
    {
        $costName = 'oxpayment';

        /** @var oxPrice $price */
        $price = oxNew('oxPrice');
        $price->setPrice(10.4);

        $oxBasket = oxNew('oxbasket');
        $oxBasket->setCost($costName, $price);

        $costs = $oxBasket->getCosts();
        $this->assertNotNull($costs);
        $this->assertTrue(is_array($costs));
        $this->assertTrue(isset($costs[$costName]));

        $p = $costs[$costName];
        $this->assertEquals(10.4, $p->getPrice());
    }

    public function testCalcInterestsCost()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $session->setVariable('paymentid', oxpsEasyCreditOxPayment::EASYCREDIT_PAYMENTID);

        $dic = $this->getMock(
            'oxpsEasyCreditDic',
            array('getSession'),
            array(null, null, null, null, null)
        );
        $dic->expects($this->any())->method('getSession')->willReturn($session);

        /** @var oxpsEasyCreditStorage $storage */
        $storage = oxNew('oxpsEasyCreditStorage', "TEST", "TEST", "TEST", 500.0);
        $storage->setInterestAmount(20.7);
        $dic->getSession()->setStorage($storage);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $interestCost = $oxBasket->calcInterestsCost();
        $this->assertNotNull($interestCost);
        $this->assertEquals(20.7, $interestCost->getPrice());
    }

    public function testCalculateBasket()
    {
        // calling calculateBasket to test _calcTotalPrice
        $oxBasket = oxNew('oxbasket');
        $oxBasket->calculateBasket();
    }
}
