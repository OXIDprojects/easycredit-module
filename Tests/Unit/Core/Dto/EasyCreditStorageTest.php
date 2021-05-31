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

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Core\Dto;

use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;

/**
 * Class EasyCreditStorageTest
 */
class EasyCreditStorageTest extends \OxidEsales\TestingLibrary\UnitTestCase
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

    public function testHasExpiredAfterCreation(): void
    {
        $oTest = new EasyCreditStorage(
            'TEST',
            'TEST',
            'TEST',
            450.0
        );

        $this->assertFalse($oTest->hasExpired());
    }

    public function testHasExpiredExpiredLastUpdate(): void
    {
        $oTest = $this->getMock(
            EasyCreditStorage::class,
            ['getStorageExpiredTimeRange'],
            ['TEST', 'TEST', 'TEST', 450.0]
        );

        sleep(2);

        $oTest->expects($this->any())->method('getStorageExpiredTimeRange')->willReturn(0);

        $this->assertTrue($oTest->hasExpired());
    }
}
