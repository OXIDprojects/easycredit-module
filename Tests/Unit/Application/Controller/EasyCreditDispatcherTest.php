<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicSession;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;

/**
 * Class oxpsEasyCreditDispatcherTest
 */
class EasyCreditDispatcherTest extends UnitTestCase
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

    protected function buildDic($oxSession)
    {
        $mockOxConfig = $this->getMock('oxConfig', [], []);

        $session = oxNew(EasyCreditDicSession::class, $oxSession);
        $mockApiConfig = oxNew(EasyCreditApiConfig::class, oxpsEasyCreditDicFactory::getApiConfig[]);
        $mockLogging = $this->getMock('EasyCreditLogging', [], [[]]);
        $mockPayloadFactory = $this->getMock('EasyCreditPayloadFactory', [], []);
        $mockDicConfig = $this->getMock('EasyCreditDicConfig', [], [$mockOxConfig]);

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

    public function testInitializeandredirect(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['isInitialized', 'initialize', 'getDic']);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(false);
        $dispatcher->expects($this->any())->method('initialize')->willReturn(null);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->initializeandredirect());
    }

    public function testGetEasyCreditDetails(): void
    {
        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['processEasyCreditDetails']);
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willReturn(null);

        $this->assertEquals('order', $dispatcher->getEasyCreditDetails());
    }

    public function testGetEasyCreditDetailsException(): void
    {
        $dic = $this->buildDic(oxNew('oxpsEasyCreditOxSession'));

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic', 'processEasyCreditDetails']);
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willThrowException(new Exception('test'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetEasyCreditDetailsDeps(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $oxBasket = $this->getMock('oxpsEasyCreditOxBasket', ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(0.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $user = oxNew('oxuser');

        $paymentHash = $this->getPaymentHash($user, $oxBasket, $dic);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic', 'call']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new stdClass();
        $entscheidung = new stdClass();
        $entscheidung->entscheidungsergebnis = oxpsEasyCreditDispatcher::INSTALMENT_DECISION_OK;
        $response->entscheidung = $entscheidung;
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('order', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedEmptyStorage(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedEmptyVorgangskennung(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            null,
            null,
            null,
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedInvalidHash(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'dummy',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testInitialize(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['isInitialized', 'getDic', 'call']);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(false);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new stdClass();
        $response->tbVorgangskennung = 'tbVorgangskennung';
        $response->fachlicheVorgangskennung = 'fachlicheVorgangskennung';
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->initializeandredirect());
    }

    public function testGetInstalmentDecision(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic', 'call']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new stdClass();
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetTbVorgangskennungNull(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['isInitialized', 'getDic', 'call']);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new stdClass();
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testLoadEasyCreditFinancialInformationWithoutStorage(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic', 'call', 'isInitialized', 'getTbVorgangskennung']);
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

    public function testGetFormattedPaymentPlan(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic', 'call', 'isInitialized']);
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

    public function testCall(): void
    {
        $session = oxNew('oxpsEasyCreditOxSession');
        $dic = $this->buildDic($session);

        $storage = oxNew(
            'EasyCreditStorage',
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(oxpsEasyCreditOxSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew('oxuser');

        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['getDic', 'isInitialized', 'getInstalmentDecision']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getInstalmentDecision')->willReturn(oxpsEasyCreditDispatcher::INSTALMENT_DECISION_OK);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetDic(): void
    {
        $dispatcher = $this->getMock('oxpsEasyCreditDispatcher', ['processEasyCreditDetails']);
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willThrowException(new Exception('test'));

        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    protected function getPaymentHash($oxUser, $oxBasket, $dic): string
    {
        return md5(json_encode($this->getCurrentInitializationData($oxUser, $oxBasket, $dic)));
    }

    protected function getCurrentInitializationData($oUser, $oBasket, $dic)
    {
        $requestBuilder = oxNew('EasyCreditInitializeRequestBuilder');

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
