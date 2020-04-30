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
 * Class oxpsEasyCreditDispatcherTest
 */
class oxpsEasyCreditDispatcherTest extends OxidTestCase
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

    protected function buildDic($oxSession)
    {
        $mockOxConfig = $this->getMock('oxConfig', array(), array());

        $session = oxNew('oxpsEasyCreditDicSession', $oxSession);
        $mockApiConfig = oxNew('oxpsEasyCreditApiConfig', oxpsEasyCreditDicFactory::getApiConfigArray());
        $mockLogging = $this->getMock('oxpsEasyCreditLogging', array(), array(array()));
        $mockPayloadFactory = $this->getMock('oxpsEasyCreditPayloadFactory', array(), array());
        $mockDicConfig = $this->getMock('oxpsEasyCreditDicConfig', array(), array($mockOxConfig));

        $mockDic = oxNew(
            'oxpseasycreditdic',
            $session,
            $mockApiConfig,
            $mockPayloadFactory,
            $mockLogging,
            $mockDicConfig
        );

        return $mockDic;
    }

    public function testInitializeandredirect()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'oxpsEasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('isInitialized', 'initialize', 'getDic'));
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(false);
        $dispatcher->expects($this->any())->method('initialize')->willReturn(null);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->initializeandredirect());
    }

    public function testGetEasyCreditDetails()
    {
        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('processEasyCreditDetails'));
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willReturn(null);

        $this->assertEquals('order', $dispatcher->getEasyCreditDetails());
    }

    public function testGetEasyCreditDetailsException()
    {
        $dic = $this->buildDic(oxNew('oxpsEasyCreditOxSession'));

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic', 'processEasyCreditDetails'));
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willThrowException(new Exception('test'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetEasyCreditDetailsDeps()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', array('getDic', 'getPrice'));
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(0.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $user = oxNew('oxuser');

        $paymentHash = $this->getPaymentHash($user, $oxBasket, $dic);

        $storage = oxNew(
            'oxpsEasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic', 'call'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new stdClass();
        $entscheidung = new stdClass();
        $entscheidung->entscheidungsergebnis = oxpsEasyCreditDispatcher::INSTALMENT_DECISION_OK;
        $response->entscheidung = $entscheidung;
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('order', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedEmptyStorage()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedEmptyVorgangskennung()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'oxpsEasyCreditStorage',
            null,
            null,
            null,
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedInvalidHash()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'oxpsEasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'dummy',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testInitialize()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('isInitialized', 'getDic', 'call'));
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(false);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new stdClass();
        $response->tbVorgangskennung = 'tbVorgangskennung';
        $response->fachlicheVorgangskennung = 'fachlicheVorgangskennung';
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->initializeandredirect());
    }

    public function testGetInstalmentDecision()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'oxpsEasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic', 'call'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new stdClass();
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetTbVorgangskennungNull()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('isInitialized', 'getDic', 'call'));
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new stdClass();
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testLoadEasyCreditFinancialInformationWithoutStorage()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic', 'call', 'isInitialized', 'getTbVorgangskennung'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getTbVorgangskennung')->willReturn('dummy');

        $response = new stdClass();
        $entscheidung = new stdClass();
        $entscheidung->entscheidungsergebnis = oxpsEasyCreditDispatcher::INSTALMENT_DECISION_OK;
        $response->entscheidung = $entscheidung;
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetFormattedPaymentPlan()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'oxpsEasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic', 'call', 'isInitialized'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);

        $dispatcher->expects($this->any())->method('call')->willReturnCallback(
            function($endpoint) {
                switch ($endpoint) {
                    case oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_DECISION:
                        $decisionResponse = new stdClass();
                        $entscheidung = new stdClass();
                        $entscheidung->entscheidungsergebnis = oxpsEasyCreditDispatcher::INSTALMENT_DECISION_OK;
                        $decisionResponse->entscheidung = $entscheidung;
                        return $decisionResponse;

                    case oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_VORGANG:
                        $vorgangResponse = new stdClass();
                        $vorgangResponse->allgemeineVorgangsdaten = 'allgemeineVorgangsdaten';
                        $vorgangResponse->tilgungsplanText = 'tilgungsplanText';
                        return $vorgangResponse;

                    case oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_FINANCIAL_INFORMATION:
                        $vorgangResponse = new stdClass();
                        $vorgangResponse->allgemeineVorgangsdaten = 'allgemeineVorgangsdaten';
                        $vorgangResponse->tilgungsplanText = 'tilgungsplanText';
                        return $vorgangResponse;

                    case oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_FINANZIERUNG:
                        $ratenPlanResponse = new stdClass();
                        $paymentPlan = new stdClass();
                        $paymentPlan->zahlungsplan = new stdClass();
                        $ratenPlanResponse->ratenplan = $paymentPlan;
                        return $ratenPlanResponse;
                }
            }
        );

        $dispatcher->setUser($user);


        $this->assertEquals('order', $dispatcher->getEasyCreditDetails());
    }

    public function testCall()
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'oxpsEasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('getDic', 'isInitialized', 'getInstalmentDecision'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getInstalmentDecision')->willReturn(oxpsEasyCreditDispatcher::INSTALMENT_DECISION_OK);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetDic()
    {
        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', array('processEasyCreditDetails'));
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willThrowException(new Exception('test'));

        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    protected function getPaymentHash($oxUser, $oxBasket, $dic)
    {
        return md5(json_encode($this->getCurrentInitializationData($oxUser, $oxBasket, $dic)));
    }

    protected function getCurrentInitializationData($oUser, $oBasket, $dic)
    {
        $requestBuilder = oxNew('oxpsEasyCreditInitializeRequestBuilder');

        $requestBuilder->setUser($oUser);
        $requestBuilder->setBasket($oBasket);
        $requestBuilder->setShippingAddress($this->getShippingAddress());

        $shopEdition = oxpsEasyCreditHelper::getShopSystem($this->getConfig()->getActiveShop());
        $requestBuilder->setShopEdition($shopEdition);

        $moduleVersion = oxpsEasyCreditHelper::getModuleVersion($dic);
        $requestBuilder->setModuleVersion($moduleVersion);

        $requestBuilder->setBaseLanguage(oxRegistry::getLang()->getBaseLanguage());

        $data = $requestBuilder->getInitializationData();
        return $data;
    }

    protected function getShippingAddress()
    {
        /** @var $oOrder oxOrder */
        $oOrder = oxNew('oxorder');
        return $oOrder->getDelAddressInfo();
    }
}
