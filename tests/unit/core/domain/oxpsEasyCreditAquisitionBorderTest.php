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
 * Class oxpsEasyCreditAquisitionBorderTest
 */
class oxpsEasyCreditAquisitionBorderTest extends OxidTestCase
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

    public function testUpdateAquisitionBorderIfNeededNoUpdate()
    {
        /** @var oxpsEasyCreditAquisitionBorder $aquisitionBorder */
        $aquisitionBorder = oxNew('oxpsEasyCreditAquisitionBorder');
        $aquisitionBorder->updateAquisitionBorderIfNeeded();
    }

    public function testUpdateAquisitionBorderIfNeededNoUpdateInvalidInterval()
    {
        $dic = oxpsEasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquBorderUpdateIntervalMin', 'XYZ');

        /** @var oxpsEasyCreditAquisitionBorder $aquisitionBorder */
        $aquisitionBorder = oxNew('oxpsEasyCreditAquisitionBorder');
        $aquisitionBorder->updateAquisitionBorderIfNeeded();
    }

    public function testUpdateAquisitionBorderIfNeededUpdateNoLastUpdate()
    {
        $dic = oxpsEasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquBorderUpdateIntervalMin', 1800);


        $webServiceClient = $this->getMock('oxpsEasyCreditWebServiceClient', array('execute'));
        $webServiceClient->expects($this->any())->method('execute')->willReturnCallback(
            function() {
                $r = new stdClass();
                $r->restbetrag = 500;
                return $r;
            }
        );

        $aquisitionBorder = $this->getMock(
            'oxpsEasyCreditAquisitionBorder',
            array('getWebServiceClient', 'getShopId')
        );
        $aquisitionBorder->expects($this->any())->method('getWebServiceClient')->willReturn($webServiceClient);
        $aquisitionBorder->expects($this->any())->method('getShopId')->willReturn(3);

        $aquisitionBorder->updateAquisitionBorderIfNeeded();
    }

    public function testUpdateAquisitionBorderIfNeededUpdateWithLastUpdate()
    {
        $dic = oxpsEasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquBorderUpdateIntervalMin', 1800);
        $dic->getConfig()->setConfigParam('oxpsECAquisitionBorderLastUpdate', date('Y-m-d H:i', 0));

        $webServiceClient = $this->getMock('oxpsEasyCreditWebServiceClient', array('execute'));
        $webServiceClient->expects($this->any())->method('execute')->willReturnCallback(
            function() {
                $r = new stdClass();
                $r->restbetrag = 500;
                return $r;
            }
        );

        $aquisitionBorder = $this->getMock(
            'oxpsEasyCreditAquisitionBorder',
            array('getWebServiceClient', 'getShopId')
        );
        $aquisitionBorder->expects($this->any())->method('getWebServiceClient')->willReturn($webServiceClient);
        $aquisitionBorder->expects($this->any())->method('getShopId')->willReturn(3);

        $aquisitionBorder->updateAquisitionBorderIfNeeded();
    }

    public function testUpdateAquisitionBorderFailure()
    {
        /** @var oxpsEasyCreditAquisitionBorder $aquisitionBorder */
        $aquisitionBorder = oxNew('oxpsEasyCreditAquisitionBorder');
        $this->assertFalse($aquisitionBorder->updateAquisitionBorder());
    }

    public function testGetCurrentAquisitionBorderValueNull()
    {
        /** @var oxpsEasyCreditAquisitionBorder $aquisitionBorder */
        $aquisitionBorder = oxNew('oxpsEasyCreditAquisitionBorder');
        $this->assertNull($aquisitionBorder->getCurrentAquisitionBorderValue());
    }

    public function testGetCurrentAquisitionBorderValue()
    {
        $dic = oxpsEasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquisitionBorderValue', 400);

        /** @var oxpsEasyCreditAquisitionBorder $aquisitionBorder */
        $aquisitionBorder = oxNew('oxpsEasyCreditAquisitionBorder');
        $borderValue = $aquisitionBorder->getCurrentAquisitionBorderValue();
        $this->assertNotNull($borderValue);
        $this->assertEquals(400.0, $borderValue);
    }

    public function testConsiderInFrontend()
    {
        /** @var oxpsEasyCreditAquisitionBorder $aquisitionBorder */
        $aquisitionBorder = oxNew('oxpsEasyCreditAquisitionBorder');
        $this->assertNotTrue($aquisitionBorder->considerInFrontend());

        $dic = oxpsEasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquBorderConsiderFrontend', true);
        $this->assertTrue($aquisitionBorder->considerInFrontend());
    }
}
