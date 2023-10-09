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
 * @package       easycredit-module
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Core\Domain;

use OxidEsales\Eshop\Core\Session;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\EasyCredit\Core\Dto\EasyCreditStorage;

/**
 * Class EasyCreditOxSessionTest
 */
class EasyCreditOxSessionTest extends UnitTestCase
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

    public function testGetStorageNoStorage(): void
    {
        $session = oxNew(Session::class);
        $this->assertNotTrue($session->getStorage());
    }

    public function testGetStorageExpiredStorage(): void
    {
        $session = oxNew(Session::class);

        $storage = $this->getMock(
            EasyCreditStorage::class,
            ['hasExpired'],
            [
                'EasyCreditStorage',
                'tbVorgangskennung',
                'fachlicheVorgangskennung',
                'authorizationHash',
                500.50
            ]
        );
        $storage->expects($this->any())->method('hasExpired')->willReturn(true);
        $session->setStorage($storage);

        $this->assertNull($session->getStorage());
    }
}