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
 * Class oxpsEasyCreditOxSessionTest
 */
class oxpsEasyCreditOxSessionTest extends OxidTestCase
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

    public function testGetStorageNoStorage()
    {
        $session = oxNew('oxsession');
        $this->assertNotTrue($session->getStorage());
    }

    public function testGetStorageExpiredStorage()
    {
        $session = oxNew('oxsession');

        $storage = $this->getMock(
            'oxpsEasyCreditStorage',
            array('hasExpired'),
            array(
                'oxpsEasyCreditStorage',
                'tbVorgangskennung',
                'fachlicheVorgangskennung',
                'authorizationHash',
                500.50
            )
        );
        $storage->expects($this->any())->method('hasExpired')->willReturn(true);
        $session->setStorage($storage);

        $this->assertNull($session->getStorage());
    }
}