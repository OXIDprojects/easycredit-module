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
 * Class oxpsEasyCreditStorageTest
 */
class oxpsEasyCreditStorageTest extends OxidTestCase
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

    public function testHasExpiredAfterCreation()
    {
        $oTest = new oxpsEasyCreditStorage(
            'TEST',
            'TEST',
            'TEST',
            450.0
        );

        $this->assertFalse($oTest->hasExpired());
    }

    public function testHasExpiredExpiredLastUpdate()
    {
        $oTest = $this->getMock(
            'oxpsEasyCreditStorage',
            array('getStorageExpiredTimeRange'),
            array('TEST', 'TEST', 'TEST', 450.0)
        );

        sleep(2);

        $oTest->expects($this->any())->method('getStorageExpiredTimeRange')->willReturn(0);

        $this->assertTrue($oTest->hasExpired());
    }
}
