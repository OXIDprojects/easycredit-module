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

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Core\Helper;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Class EasyCreditHelperTest
 */
class EasyCreditHelperTest extends UnitTestCase
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

    public function testGetExampleCalculationPriceFromBasket(): void
    {
        $price = oxNew(Price::class);
        $price->setPrice(450.99);

        $basket = $this->getMock(Basket::class);
        $basket->expects($this->any())->method('getPrice')->willReturn($price);

        $this->assertEquals($price, EasyCreditHelper::getExampleCalculationPrice(null, $basket));
    }

    public function testHasPackstationFormatNormal(): void
    {
        $this->assertFalse(EasyCreditHelper::hasPackstationFormat('Teststraße', '7'));
    }

    public function testHasPackstationFormatNumericNoNumericPackStation1(): void
    {
        $this->assertFalse(EasyCreditHelper::hasPackstationFormat('014', ''));
    }

    public function testHasPackstationFormatNumericNoNumericPackStation2(): void
    {
        $this->assertFalse(EasyCreditHelper::hasPackstationFormat('', '014'));
    }

    public function testHasPackstationFormatNumericPackStation(): void
    {
        $this->assertTrue(EasyCreditHelper::hasPackstationFormat('014', '4711'));
    }

    public function testHasPackstationFormatNonNumericPackStation1(): void
    {
        $this->assertTrue(EasyCreditHelper::hasPackstationFormat('Packstation 014', ''));
    }

    public function testHasPackstationFormatNonNumericPackStation2(): void
    {
        $this->assertTrue(EasyCreditHelper::hasPackstationFormat('', 'Packstation 4711'));
    }

    public function testGetShopSystemCE(): void
    {
        $shop                     = oxNew(Shop::class);
        $shop->oxshops__oxedition = new Field('CE');

        $this->assertEquals('Community Edition', EasyCreditHelper::getShopSystem($shop));
    }

    public function testGetShopSystemPE(): void
    {
        $shop                     = oxNew(Shop::class);
        $shop->oxshops__oxedition = new Field('PE');

        $this->assertEquals('Professional Edition', EasyCreditHelper::getShopSystem($shop));
    }

    public function testGetShopSystemEE(): void
    {
        $shop                     = oxNew(Shop::class);
        $shop->oxshops__oxedition = new Field('EE');

        $this->assertEquals('Enterprise Edition', EasyCreditHelper::getShopSystem($shop));
    }

    public function testGetModuleVersionOk(): void
    {
        $apiConfig = oxNew(EasyCreditApiConfig::class, []);
        $dic       = oxNew(EasyCreditDic::class, null, $apiConfig, null, null, null);

        $this->assertEquals('3.0.0-dev', EasyCreditHelper::getModuleVersion($dic));
    }

    public function testGetModuleVersionWrongModuleId(): void
    {
        $apiConfig = $this->getMock(EasyCreditApiConfig::class, ['getEasyCreditModuleId'], [[]]);
        $apiConfig->expects($this->any())->method('getEasyCreditModuleId')->willReturn('dummy');
        $dic = oxNew(EasyCreditDic::class, null, $apiConfig, null, null, null);

        $this->assertEquals('', EasyCreditHelper::getModuleVersion($dic));
    }
}