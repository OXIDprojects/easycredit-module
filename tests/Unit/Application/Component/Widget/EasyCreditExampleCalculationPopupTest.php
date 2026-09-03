<?php

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Component\Widget;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Component\Widget\EasyCreditExampleCalculationPopup;
/**
 * Class EasyCreditExampleCalculationPopupTest
 */
class EasyCreditExampleCalculationPopupTest extends TestCase
{
    private $shopkennung = null;

    /**
     * Set up test environment
     *
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->shopkennung = Registry::getConfig()->getConfigParam('oxpsECWebshopId');
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
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $popup = oxNew(EasyCreditExampleCalculationPopup::class);
        if (true === $apiConfig->config['oxpsECUseV3']) {
            $this->assertEquals('https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.esm.js', $popup->getIFrameUrl());
        } else {
            $this->assertEquals('https://ratenkauf.easycredit.de/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf?shopKennung='. $this->shopkennung .'&bestellwert=0', $popup->getIFrameUrl());
        }

        UtilsObject::resetClassInstances();
    }
}
