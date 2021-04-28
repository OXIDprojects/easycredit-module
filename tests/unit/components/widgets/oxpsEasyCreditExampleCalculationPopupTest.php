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
 * Class oxpsEasyCreditExampleCalculationPopupTest
 */
class oxpsEasyCreditExampleCalculationPopupTest extends OxidTestCase
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

    public function testGetExampleCalculationRate()
    {
        $popup = oxNew('EasyCreditExampleCalculationPopup');
        $this->assertNotNull($popup->getDic());
    }

    public function testGetBasket()
    {
        $popup = oxNew('EasyCreditExampleCalculationPopup');
        $basket = $popup->getBasket();
        $this->assertNotNull($basket);
        $price = $basket->getPrice();
        $this->assertNotNull($price);
        $this->assertEquals(0.0, $price->getPrice());
    }

    public function testGetPrice()
    {
        $popup = oxNew('EasyCreditExampleCalculationPopup');
        $price = $popup->getPrice();
        $this->assertNotNull($price);
        $this->assertEquals(0.0, $price->getPrice());
    }

    public function testGetIFrameUrl()
    {
        $popup = oxNew('EasyCreditExampleCalculationPopup');
        $this->assertEquals('https://ratenkauf.easycredit.de/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf?shopKennung=&bestellwert=0', $popup->getIFrameUrl());
    }
}
