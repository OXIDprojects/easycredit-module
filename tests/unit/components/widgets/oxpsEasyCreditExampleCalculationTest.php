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
 * Class oxpsEasyCreditExampleCalculationTest
 */
class oxpsEasyCreditExampleCalculationTest extends OxidTestCase
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
        $calculation = oxNew('oxpseasycreditexamplecalculation');

        $this->assertNull($calculation->getExampleCalculationRate());
    }

    public function testGetExampleCalculationRateHasExampleCalculation()
    {
        $calculation = $this->getMock('oxpseasycreditexamplecalculation', array('hasExampleCalculation'));
        $calculation->expects($this->any())->method('hasExampleCalculation')->willReturn(true);

        $this->assertEquals('0,00', $calculation->getExampleCalculationRate());
    }

    public function testHasExampleCalculation()
    {
        $calculation = oxNew('oxpseasycreditexamplecalculation');

        $this->assertFalse($calculation->hasExampleCalculation());
    }

    public function testGetExampleCalulation()
    {
        $response = "dummy";

        $calculation = $this->getMock('oxpseasycreditexamplecalculation', array('getExampleCalculationResponse'));
        $calculation->expects($this->any())->method('getExampleCalculationResponse')->willReturn($response);

        $this->assertEquals($response, $calculation->getExampleCalulation());
    }

    public function testGetExampleCalculationResponse()
    {
        $calculation = $this->getMock('oxpseasycreditexamplecalculation', array('getPrice'));
        $calculation->expects($this->any())->method('getPrice')->willReturn(false);

        $this->assertFalse($calculation->getExampleCalculationResponse());
    }

    public function testGetAjaxUrl()
    {
        $calculation = oxNew('oxpseasycreditexamplecalculation');

        $this->assertStringEndsWith('index.php?cl=oxpseasycreditexamplecalculation&placeholderId=&ajax=1', $calculation->getAjaxUrl());
    }

    public function testGetPopupAjaxUrl()
    {
        $calculation = oxNew('oxpseasycreditexamplecalculation');

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $this->assertEquals($sslShopUrl . 'index.php?cl=oxpseasycreditexamplecalculationpopup&ajax=1', $calculation->getPopupAjaxUrl());
    }

    public function testIsAjax()
    {
        $calculation = oxNew('oxpseasycreditexamplecalculation');

        $this->assertFalse($calculation->isAjax());
    }
}
