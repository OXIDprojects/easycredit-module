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
 * Class oxpsEasyCreditOrderEasyCreditTest
 */
class oxpsEasyCreditOrderEasyCreditTest extends OxidTestCase
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
        $controller = oxNew('oxpseasycreditordereasycredit');
        $this->assertEquals('oxpseasycredit_order_easycredit.tpl', $controller->render());
    }

    public function testRenderWithEditObjectId()
    {
        $controller = $this->getMock('oxpseasycreditordereasycredit', array('getEditObjectId', 'hasEasyCreditPayment'));
        $controller->expects($this->any())->method('getEditObjectId')->willReturn('1');
        $controller->expects($this->any())->method('hasEasyCreditPayment')->willReturn(true);

        $this->assertEquals('oxpseasycredit_order_easycredit.tpl', $controller->render());
    }

    public function testGetEasyCreditConfirmationResponse()
    {
        $response = new stdClass();
        $response->result = 'test';

        $order = oxNew('oxorder');
        $order->oxorder__ecredconfirmresponse = new oxField(base64_encode(serialize($response)));

        $controller = $this->getMock('oxpseasycreditordereasycredit', array('getOrder'));
        $controller->expects($this->any())->method('getOrder')->willReturn($order);

        $this->assertEquals('{
    "result": "test"
}', $controller->getEasyCreditConfirmationResponse());
    }
}