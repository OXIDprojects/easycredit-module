<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Component\Widget;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Component\Widget\EasyCreditExampleCalculationPopup;

/**
 * Class EasyCreditExampleCalculationPopupTest
 */
class EasyCreditExampleCalculationPopupTest extends UnitTestCase
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

    public function testGetExampleCalculationRate(): void
    {
        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        $this->assertNotNull($popup->getDic());
    }

    public function testGetBasket(): void
    {
        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        $basket = $popup->getBasket();
        $this->assertNotNull($basket);
        $price = $basket->getPrice();
        $this->assertNotNull($price);
        $this->assertEquals(0.0, $price->getPrice());
    }

    public function testGetPrice(): void
    {
        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        $price = $popup->getPrice();
        $this->assertNotNull($price);
        $this->assertEquals(0.0, $price->getPrice());
    }

    public function testGetIFrameUrl(): void
    {
        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        $this->assertEquals('https://ratenkauf.easycredit.de/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf?shopKennung=&bestellwert=0', $popup->getIFrameUrl());
    }
}
