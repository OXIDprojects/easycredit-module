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
 * Class oxpsEasyCreditOrder_AddressTest
 */
class oxpsEasyCreditOrder_AddressTest extends OxidTestCase
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

    public function testRender()
    {
        $controller = oxNew('oxpseasycreditorder_address');
        $this->assertEquals('order_address.tpl', $controller->render());
    }

    public function testRenderWithEditObjectId()
    {
        $controller = $this->getMock('oxpseasycreditorder_address', array('getEditObjectId'));
        $controller->expects($this->any())->method('getEditObjectId')->willReturn('1');

        $this->assertEquals('order_address.tpl', $controller->render());
    }
}