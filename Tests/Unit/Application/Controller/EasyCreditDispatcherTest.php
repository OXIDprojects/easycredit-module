<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Application\Controller\EasyCreditDispatcherController;
use OxidProfessionalServices\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicSession;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditBasket;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditSession;
use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditInitializeRequestBuilder;
use OxidProfessionalServices\EasyCredit\Core\PayLoad\EasyCreditPayloadFactory;

/**
 * Class EasyCreditDispatcherControllerTest
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
        $mockLogging = $this->getMock(EasyCreditLogging::class, [], [[]]);
        $mockPayloadFactory = $this->getMock(EasyCreditPayloadFactory::class, [], []);
        $mockDicConfig = $this->getMock(EasyCreditDicConfig::class, [], [$mockOxConfig]);

        $mockDic = oxNew(
            EasyCreditDic::class,
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
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['isInitialized', 'initialize', 'getDic']);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(false);
        $dispatcher->expects($this->any())->method('initialize')->willReturn(null);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->initializeandredirect());
    }

    public function testGetEasyCreditDetails(): void
    {
        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['processEasyCreditDetails']);
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willReturn(null);

        $this->assertEquals('order', $dispatcher->getEasyCreditDetails());
    }

    public function testGetEasyCreditDetailsException(): void
    {
        $dic = $this->buildDic(oxNew(EasyCreditSession::class));

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic', 'processEasyCreditDetails']);
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willThrowException(new \Exception('test'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetEasyCreditDetailsDeps(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $oxBasket = $this->getMock(EasyCreditBasket::class, ['getDic', 'getPrice']);
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(0.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);

        $user = oxNew(User::class);

        $paymentHash = $this->getPaymentHash($user, $oxBasket, $dic);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            $paymentHash,
            0.0
        );
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic', 'call']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new \stdClass();
        $entscheidung = new \stdClass();
        $entscheidung->entscheidungsergebnis = EasyCreditDispatcherController::INSTALMENT_DECISION_OK;
        $response->entscheidung = $entscheidung;
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('order', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedEmptyStorage(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedEmptyVorgangskennung(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            null,
            null,
            null,
            0.0
        );
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testIsInitializedInvalidHash(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'dummy',
            0.0
        );
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testInitialize(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['isInitialized', 'getDic', 'call']);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(false);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new \stdClass();
        $response->tbVorgangskennung = 'tbVorgangskennung';
        $response->fachlicheVorgangskennung = 'fachlicheVorgangskennung';
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->initializeandredirect());
    }

    public function testGetInstalmentDecision(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic', 'call']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new \stdClass();
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetTbVorgangskennungNull(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['isInitialized', 'getDic', 'call']);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new \stdClass();
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testLoadEasyCreditFinancialInformationWithoutStorage(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic', 'call', 'isInitialized', 'getTbVorgangskennung']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getTbVorgangskennung')->willReturn('dummy');

        $response = new \stdClass();
        $entscheidung = new \stdClass();
        $entscheidung->entscheidungsergebnis = EasyCreditDispatcherController::INSTALMENT_DECISION_OK;
        $response->entscheidung = $entscheidung;
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetFormattedPaymentPlan(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic', 'call', 'isInitialized']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);

        $dispatcher->expects($this->any())->method('call')->willReturnCallback(
            function($endpoint) {
                switch ($endpoint) {
                    case EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_DECISION:
                        $decisionResponse = new \stdClass();
                        $entscheidung = new \stdClass();
                        $entscheidung->entscheidungsergebnis = EasyCreditDispatcherController::INSTALMENT_DECISION_OK;
                        $decisionResponse->entscheidung = $entscheidung;
                        return $decisionResponse;

                    case EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_VORGANG:
                        $vorgangResponse = new \stdClass();
                        $vorgangResponse->allgemeineVorgangsdaten = 'allgemeineVorgangsdaten';
                        $vorgangResponse->tilgungsplanText = 'tilgungsplanText';
                        return $vorgangResponse;

                    case EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_FINANCIAL_INFORMATION:
                        $vorgangResponse = new \stdClass();
                        $vorgangResponse->allgemeineVorgangsdaten = 'allgemeineVorgangsdaten';
                        $vorgangResponse->tilgungsplanText = 'tilgungsplanText';
                        return $vorgangResponse;

                    case EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_FINANZIERUNG:
                        $ratenPlanResponse = new \stdClass();
                        $paymentPlan = new \stdClass();
                        $paymentPlan->zahlungsplan = new \stdClass();
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
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $storage = oxNew(
            EasyCreditStorage::class,
            'tbVorgangskennung',
            'fachlicheVorgangskennung',
            'b8d01510bbbf5fe767f068122ba0b0c4',
            0.0
        );
        $session->setVariable(EasyCreditSession::API_CONFIG_STORAGE, serialize($storage));

        $user = oxNew(User::class);

        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['getDic', 'isInitialized', 'getInstalmentDecision']);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getInstalmentDecision')->willReturn(EasyCreditDispatcherController::INSTALMENT_DECISION_OK);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    public function testGetDic(): void
    {
        $dispatcher = $this->getMock(EasyCreditDispatcherController::class, ['processEasyCreditDetails']);
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willThrowException(new \Exception('test'));

        $this->assertEquals('payment', $dispatcher->getEasyCreditDetails());
    }

    protected function getPaymentHash($oxUser, $oxBasket, $dic): string
    {
        return md5(json_encode($this->getCurrentInitializationData($oxUser, $oxBasket, $dic)));
    }

    protected function getCurrentInitializationData($oUser, $oBasket, $dic)
    {
        $requestBuilder = oxNew(EasyCreditInitializeRequestBuilder::class);

        $requestBuilder->setUser($oUser);
        $requestBuilder->setBasket($oBasket);
        $requestBuilder->setShippingAddress($this->getShippingAddress());

        $shopEdition = oxpsEasyCreditHelper::getShopSystem($this->getConfig()->getActiveShop());
        $requestBuilder->setShopEdition($shopEdition);

        $moduleVersion = oxpsEasyCreditHelper::getModuleVersion($dic);
        $requestBuilder->setModuleVersion($moduleVersion);

        $requestBuilder->setBaseLanguage(oxRegistry::getLang()->getBaseLanguage());

        return $requestBuilder->getInitializationData();
    }

    protected function getShippingAddress()
    {
        /** @var $oOrder Order */
        $oOrder = oxNew(Order::class);
        return $oOrder->getDelAddressInfo();
    }
}
