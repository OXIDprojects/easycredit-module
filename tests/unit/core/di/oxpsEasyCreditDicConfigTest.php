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
 * Class oxpsEasyCreditDicConfigTest
 */
class oxpsEasyCreditDicConfigTest extends OxidTestCase
{
    const SHOP_ID = '3';

    const SSL_SHOP_URL = 'https://test.url';

    const CONFIG_GET_PARAM_NAME = 'TESTPARAMGET';
    const CONFIG_GET_PARAM_VALUE = 'TESTVALUEGET';

    const CONFIG_SET_PARAM_NAME = 'TESTPARAMSET';
    const CONFIG_SET_PARAM_VALUE = 'TESTVALUESET';

    /** @var oxpsEasyCreditDicConfig */
    private $dicConfig;

    /** @var array */
    private $configStore;

    private $sVarType;
    private $sVarName;
    private $sVarVal;
    private $sShopId;
    private $sModule;

    /**
     * Set up test environment
     *
     * @return null
     * @throws oxSystemComponentException
     */
    public function setUp()
    {
        parent::setUp();

        $this->configStore = array();
        $this->configStore[self::CONFIG_GET_PARAM_NAME] = self::CONFIG_GET_PARAM_VALUE;

        $oxConfig = $this->getMock(
            'oxConfig',
            array('getShopId', 'getSslShopUrl', 'getConfigParam', 'setConfigParam', 'saveShopConfVar')
        );

        $oxConfig->expects($this->any())->method('getShopId')->willReturn(self::SHOP_ID);
        $oxConfig->expects($this->any())->method('getSslShopUrl')->willReturn(self::SSL_SHOP_URL);

        $oxConfig->expects($this->any())->method('getConfigParam')->willReturnCallback(
            function($key) {
                return $this->configStore[$key];
            }
        );

        $oxConfig->expects($this->any())->method('setConfigParam')->willReturnCallback(
            function($key, $value) {
                $this->configStore[$key] = $value;
            }
        );

        $oxConfig->expects($this->any())->method('saveShopConfVar')->willReturnCallback(
            function($sVarType, $sVarName, $sVarVal, $sShopId, $sModule) {
                $this->sVarType = $sVarType;
                $this->sVarName = $sVarName;
                $this->sVarVal  = $sVarVal;
                $this->sShopId  = $sShopId;
                $this->sModule  = $sModule;
            }
        );

        $this->dicConfig = oxNew('oxpsEasyCreditDicConfig', $oxConfig);
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

    public function testGetShopId()
    {
        $this->assertEquals(self::SHOP_ID, $this->dicConfig->getShopId());
    }

    public function testGetSslShopUrl()
    {
        $this->assertEquals(self::SSL_SHOP_URL, $this->dicConfig->getSslShopUrl());
    }

    public function testGetConfigParam()
    {
        $this->assertEquals(self::CONFIG_GET_PARAM_VALUE, $this->dicConfig->getConfigParam(self::CONFIG_GET_PARAM_NAME));
    }

    public function testSetConfigParam()
    {
        $this->assertNull($this->dicConfig->getConfigParam(self::CONFIG_SET_PARAM_NAME));
        $this->dicConfig->setConfigParam(self::CONFIG_SET_PARAM_NAME, self::CONFIG_SET_PARAM_VALUE);
        $this->assertEquals(self::CONFIG_SET_PARAM_VALUE, $this->dicConfig->getConfigParam(self::CONFIG_SET_PARAM_NAME));
    }

    public function testSaveShopConfVar()
    {
        $sVarType = 'str';
        $sVarName = 'oxpsEasyCreditTest';
        $sVarVal = 'TEST';
        $sShopId = '3';
        $sModule = 'module:oxpseasycredit';

        $this->dicConfig->saveShopConfVar(
            $sVarType, $sVarName, $sVarVal, $sShopId, $sModule
        );

        $this->assertEquals($sVarType, $this->sVarType);
        $this->assertEquals($sVarName, $this->sVarName);
        $this->assertEquals($sVarVal, $this->sVarVal);
        $this->assertEquals($sShopId, $this->sShopId);
        $this->assertEquals($sModule, $this->sModule);
    }
}
