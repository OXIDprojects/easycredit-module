<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Component\Widget;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Component\Widget\EasyCreditExampleCalculation;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;

/**
 * Class EasyCreditExampleCalculationTest
 */
class EasyCreditExampleCalculationTest extends UnitTestCase
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

    public function testGetExampleCalculationRate(): void
    {
        $calculation = oxNew(EasyCreditExampleCalculation::class);

        $this->assertNull($calculation->getExampleCalculationRate());
    }

    public function testGetExampleCalculationRateHasExampleCalculation(): void
    {
        $calculation = $this->getMock(EasyCreditExampleCalculation::class, ['hasExampleCalculation']);
        $calculation->expects($this->any())->method('hasExampleCalculation')->willReturn(true);

        $this->assertEquals('0,00', $calculation->getExampleCalculationRate());
    }

    public function testHasExampleCalculation(): void
    {
        $calculation = oxNew(EasyCreditExampleCalculation::class);

        $this->assertFalse($calculation->hasExampleCalculation());
    }

    public function testGetExampleCalulation(): void
    {
        $response = "dummy";

        $calculation = $this->getMock(EasyCreditExampleCalculation::class, ['getExampleCalculationResponse']);
        $calculation->expects($this->any())->method('getExampleCalculationResponse')->willReturn($response);

        $this->assertEquals($response, $calculation->getExampleCalulation());
    }

    public function testGetExampleCalculationResponse(): void
    {
        $calculation = $this->getMock(EasyCreditExampleCalculation::class, ['getPrice']);
        $calculation->expects($this->any())->method('getPrice')->willReturn(false);

        $this->assertFalse($calculation->getExampleCalculationResponse());
    }

    public function testGetAjaxUrl(): void
    {
        $calculation = oxNew(EasyCreditExampleCalculation::class);

        $this->assertStringEndsWith('index.php?cl=easycreditexamplecalculation&placeholderId=&ajax=1', $calculation->getAjaxUrl());
    }

    public function testGetPopupAjaxUrl(): void
    {
        $calculation = oxNew(EasyCreditExampleCalculation::class);

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $this->assertEquals($sslShopUrl . 'index.php?cl=easycreditexamplecalculationpopup&ajax=1', $calculation->getPopupAjaxUrl());
    }

    public function testIsAjax(): void
    {
        $calculation = oxNew(EasyCreditExampleCalculation::class);

        $this->assertFalse($calculation->isAjax());
    }
}
