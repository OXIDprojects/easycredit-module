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
 * Class oxpsEasyCreditHelperTest
 */
class oxpsEasyCreditHelperTest extends OxidTestCase
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

    public function testGetExampleCalculationPriceFromBasket()
    {
        $price = oxNew('oxprice');
        $price->setPrice(450.99);

        $basket = $this->getMock('oxbasket');
        $basket->expects($this->any())->method('getPrice')->willReturn($price);

        $this->assertEquals($price, oxpsEasyCreditHelper::getExampleCalculationPrice(null, $basket));
    }

    public function testHasPackstationFormatNormal()
    {
        $this->assertFalse(oxpsEasyCreditHelper::hasPackstationFormat('Teststraße', '7'));
    }

    public function testHasPackstationFormatNumericNoNumericPackStation1()
    {
        $this->assertFalse(oxpsEasyCreditHelper::hasPackstationFormat('014', ''));
    }

    public function testHasPackstationFormatNumericNoNumericPackStation2()
    {
        $this->assertFalse(oxpsEasyCreditHelper::hasPackstationFormat('', '014'));
    }

    public function testHasPackstationFormatNumericPackStation()
    {
        $this->assertTrue(oxpsEasyCreditHelper::hasPackstationFormat('014', '4711'));
    }

    public function testHasPackstationFormatNonNumericPackStation1()
    {
        $this->assertTrue(oxpsEasyCreditHelper::hasPackstationFormat('Packstation 014', ''));
    }

    public function testHasPackstationFormatNonNumericPackStation2()
    {
        $this->assertTrue(oxpsEasyCreditHelper::hasPackstationFormat('', 'Packstation 4711'));
    }

    public function testGetShopSystemCE()
    {
        $shop = oxNew('oxshop');
        $shop->oxshops__oxedition = new oxField('CE');

        $this->assertEquals('Community Edition', oxpsEasyCreditHelper::getShopSystem($shop));
    }

    public function testGetShopSystemPE()
    {
        $shop = oxNew('oxshop');
        $shop->oxshops__oxedition = new oxField('PE');

        $this->assertEquals('Professional Edition', oxpsEasyCreditHelper::getShopSystem($shop));
    }

    public function testGetShopSystemEE()
    {
        $shop = oxNew('oxshop');
        $shop->oxshops__oxedition = new oxField('EE');

        $this->assertEquals('Enterprise Edition', oxpsEasyCreditHelper::getShopSystem($shop));
    }

    public function testGetModuleVersionOk()
    {
        $apiConfig = oxNew(EasyCreditApiConfig::class, array());
        $dic = oxNew('EasyCreditDic', null, $apiConfig, null, null, null);

        $this->assertEquals('1.0.1', oxpsEasyCreditHelper::getModuleVersion($dic));
    }

    public function testGetModuleVersionWrongModuleId()
    {
        $apiConfig = $this->getMock('oxpsEasyCreditApiConfig', array('getEasyCreditModuleId'), array(array()));
        $apiConfig->expects($this->any())->method('getEasyCreditModuleId')->willReturn('dummy');
        $dic = oxNew('EasyCreditDic', null, $apiConfig, null, null, null);

        $this->assertEquals('', oxpsEasyCreditHelper::getModuleVersion($dic));
    }
}