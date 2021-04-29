<?php

namespace OxidProfessionalServices\EasyCredit\Tests\UnitApplication\Component\Widget;

use OxidProfessionalServices\EasyCredit\Application\Component\Widget\EasyCreditExampleCalculationPopup;

/**
 * Class EasyCreditExampleCalculationPopupTest
 */
class EasyCreditExampleCalculationPopupTest extends OxidTestCase
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
        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        $this->assertNotNull($popup->getDic());
    }

    public function testGetBasket()
    {
        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        $basket = $popup->getBasket();
        $this->assertNotNull($basket);
        $price = $basket->getPrice();
        $this->assertNotNull($price);
        $this->assertEquals(0.0, $price->getPrice());
    }

    public function testGetPrice()
    {
        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        $price = $popup->getPrice();
        $this->assertNotNull($price);
        $this->assertEquals(0.0, $price->getPrice());
    }

    public function testGetIFrameUrl()
    {
        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        $this->assertEquals('https://ratenkauf.easycredit.de/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf?shopKennung=&bestellwert=0', $popup->getIFrameUrl());
    }
}
