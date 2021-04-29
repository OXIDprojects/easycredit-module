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

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Core\Domain;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClient;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditAquisitionBorder;

/**
 * Class EasyCreditAquisitionBorderTest
 */
class EasyCreditAquisitionBorderTest extends UnitTestCase
{
    /**
     * Set up test environment
     *
     * @return null
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     * @return null
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testUpdateAquisitionBorderIfNeededNoUpdate(): void
    {
        $aquisitionBorder = oxNew(EasyCreditAquisitionBorder::class);
        $aquisitionBorder->updateAquisitionBorderIfNeeded();
    }

    public function testUpdateAquisitionBorderIfNeededNoUpdateInvalidInterval(): void
    {
        $dic = EasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquBorderUpdateIntervalMin', 'XYZ');

        $aquisitionBorder = oxNew(EasyCreditAquisitionBorder::class);
        $aquisitionBorder->updateAquisitionBorderIfNeeded();
    }

    public function testUpdateAquisitionBorderIfNeededUpdateNoLastUpdate(): void
    {
        $dic = EasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquBorderUpdateIntervalMin', 1800);


        $webServiceClient = $this->getMock(EasyCreditWebServiceClient::class, array('execute'));
        $webServiceClient->expects($this->any())->method('execute')->willReturnCallback(
            function () {
                $r             = new \stdClass();
                $r->restbetrag = 500;
                return $r;
            }
        );

        $aquisitionBorder = $this->getMock(
            EasyCreditAquisitionBorder::class,
            array('getWebServiceClient', 'getShopId')
        );
        $aquisitionBorder->expects($this->any())->method('getWebServiceClient')->willReturn($webServiceClient);
        $aquisitionBorder->expects($this->any())->method('getShopId')->willReturn(3);

        $aquisitionBorder->updateAquisitionBorderIfNeeded();
    }

    public function testUpdateAquisitionBorderIfNeededUpdateWithLastUpdate(): void
    {
        $dic = EasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquBorderUpdateIntervalMin', 1800);
        $dic->getConfig()->setConfigParam('oxpsECAquisitionBorderLastUpdate', date('Y-m-d H:i', 0));

        $webServiceClient = $this->getMock(EasyCreditWebServiceClient::class, array('execute'));
        $webServiceClient->expects($this->any())->method('execute')->willReturnCallback(
            function () {
                $r             = new \stdClass();
                $r->restbetrag = 500;
                return $r;
            }
        );

        $aquisitionBorder = $this->getMock(
            EasyCreditAquisitionBorder::class,
            array('getWebServiceClient', 'getShopId')
        );
        $aquisitionBorder->expects($this->any())->method('getWebServiceClient')->willReturn($webServiceClient);
        $aquisitionBorder->expects($this->any())->method('getShopId')->willReturn(3);

        $aquisitionBorder->updateAquisitionBorderIfNeeded();
    }

    public function testUpdateAquisitionBorderFailure(): void
    {
        $aquisitionBorder = oxNew(EasyCreditAquisitionBorder::class);
        $this->assertFalse($aquisitionBorder->updateAquisitionBorder());
    }

    public function testGetCurrentAquisitionBorderValueNull(): void
    {
        $aquisitionBorder = oxNew(EasyCreditAquisitionBorder::class);
        $this->assertNull($aquisitionBorder->getCurrentAquisitionBorderValue());
    }

    public function testGetCurrentAquisitionBorderValue(): void
    {
        $dic = EasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquisitionBorderValue', 400);

        $aquisitionBorder = oxNew(EasyCreditAquisitionBorder::class);
        $borderValue      = $aquisitionBorder->getCurrentAquisitionBorderValue();
        $this->assertNotNull($borderValue);
        $this->assertEquals(400.0, $borderValue);
    }

    public function testConsiderInFrontend(): void
    {
        $aquisitionBorder = oxNew(EasyCreditAquisitionBorder::class);
        $this->assertNotTrue($aquisitionBorder->considerInFrontend());

        $dic = EasyCreditDicFactory::getDic();
        $dic->getConfig()->setConfigParam('oxpsECAquBorderConsiderFrontend', true);
        $this->assertTrue($aquisitionBorder->considerInFrontend());
    }
}
