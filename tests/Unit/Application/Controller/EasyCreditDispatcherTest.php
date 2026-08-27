<?php

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Application\Controller;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Controller\EasyCreditDispatcherController;
use OxidSolutionCatalysts\EasyCredit\Core\CrossCutting\EasyCreditLogging;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDic;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicSession;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditBasket;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditSession;
use OxidSolutionCatalysts\EasyCredit\Core\Dto\EasyCreditStorage;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditHelper;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditInitializeRequestBuilder;

/**
 * Class EasyCreditDispatcherControllerTest
 */
class EasyCreditDispatcherTest extends TestCase
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

    protected function buildDic($oxSession, $isV3 = false)
    {
        $session = oxNew(EasyCreditDicSession::class, $oxSession);
        $mockApiConfig = oxNew(EasyCreditApiConfig::class, EasyCreditDicFactory::getApiConfigArray());
        $mockLogging = $this->getMockBuilder(EasyCreditLogging::class)->disableOriginalConstructor()->getMock();
        $mockDicConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        if ($isV3) {
            $mockApiConfig->config["oxpsECUseV3"] = true;
        } else {
            $mockApiConfig->config["oxpsECUseV3"] = false;
        }

        $mockDic = oxNew(
            EasyCreditDic::class,
            $session,
            $mockApiConfig,
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

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isInitialized',
                'initialize',
                'getDic',
            ])
            ->getMock();
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(false);
        $dispatcher->expects($this->any())->method('initialize')->willReturn(null);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->initializeandredirectInstallment());
    }

    public function testGetEasyCreditDetails(): void
    {
        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods(['processEasyCreditDetails',
                'getInstalmentDecision', 
                'checkInitialization',
                'checkAuthorization',
                'loadEasyCreditFinancialInformation'])
            ->getMock();
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willReturn(null);
        $dispatcher->expects($this->any())->method('getInstalmentDecision')->willReturn(null);
        $dispatcher->expects($this->any())->method('checkInitialization')->willReturn(null);
        $dispatcher->expects($this->any())->method('checkAuthorization')->willReturn(null);
        $dispatcher->expects($this->any())->method('loadEasyCreditFinancialInformation')->willReturn(null);

        $this->assertEquals('order', $dispatcher->getEasyCreditInstallmentDetails());
    }

    public function testGetEasyCreditDetailsException(): void
    {
        $dic = $this->buildDic(oxNew(EasyCreditSession::class));

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
                'processEasyCreditDetails',
            ])
            ->getMock();
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willThrowException(new \Exception('test'));
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $user = oxNew(User::class);
        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
    }

    public function testGetEasyCreditDetailsDeps(): void
    {
        Registry::getSession()->setVariable('paymentid', EasyCreditHelper::EASYCREDIT_INSTALLMENT_PAYMENTID);
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $oxBasket = $this->getMockBuilder(EasyCreditBasket::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
                'getPrice',
                'getPaymentId'
            ])
            ->getMock();
        $oxBasket->expects($this->any())->method('getDic')->willReturn($dic);

        $price = oxNew('oxprice');
        $price->setPrice(0.0);
        $oxBasket->expects($this->any())->method('getPrice')->willReturn($price);
        $oxBasket->expects($this->any())->method('getPaymentId')->willReturn(EasyCreditHelper::EASYCREDIT_INSTALLMENT_PAYMENTID);

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

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
                'call',
            ])
            ->getMock();
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new \stdClass();
        $entscheidung = new \stdClass();
        $entscheidung->entscheidungsergebnis = EasyCreditDispatcherController::INSTALMENT_DECISION_OK;
        $response->entscheidung = $entscheidung;
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);

        $this->assertEquals('order', $dispatcher->getEasyCreditInstallmentDetails());
    }

    public function testIsInitializedEmptyStorage(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $user = oxNew(User::class);

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDic'])
            ->getMock();
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
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

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDic'])
            ->getMock();
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
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

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDic'])
            ->getMock();
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
    }

    public function testInitialize(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $user = oxNew(User::class);

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isInitialized',
                'getDic',
                'call',
            ])
            ->getMock();
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(false);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new \stdClass();
        $response->tbVorgangskennung = 'tbVorgangskennung';
        $response->fachlicheVorgangskennung = 'fachlicheVorgangskennung';
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->initializeandredirectInstallment());
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

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDic',
                'call',
            ])
            ->getMock();
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new \stdClass();
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
    }

    public function testGetTbVorgangskennungNull(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $user = oxNew(User::class);

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isInitialized',
                'getDic',
                'call',
            ])
            ->getMock();
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);

        $response = new \stdClass();
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
    }

    public function testLoadEasyCreditFinancialInformationWithoutStorage(): void
    {
        $session = oxNew(EasyCreditSession::class);
        $dic = $this->buildDic($session);

        $user = oxNew(User::class);

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isInitialized',
                'getDic',
                'call',
                'getTbVorgangskennung',
            ])
            ->getMock();
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getTbVorgangskennung')->willReturn('dummy');

        $response = new \stdClass();
        $entscheidung = new \stdClass();
        $entscheidung->entscheidungsergebnis = EasyCreditDispatcherController::INSTALMENT_DECISION_OK;
        $response->entscheidung = $entscheidung;
        $dispatcher->expects($this->any())->method('call')->willReturn($response);

        $dispatcher->setUser($user);


        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
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

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isInitialized',
                'getDic',
                'call',
            ])
            ->getMock();
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


        $this->assertEquals('order', $dispatcher->getEasyCreditInstallmentDetails());
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

        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isInitialized',
                'getDic',
                'getInstalmentDecision',
            ])
            ->getMock();
        $dispatcher->expects($this->any())->method('getDic')->willReturn($dic);
        $dispatcher->expects($this->any())->method('isInitialized')->willReturn(true);
        $dispatcher->expects($this->any())->method('getInstalmentDecision')->willReturn(EasyCreditDispatcherController::INSTALMENT_DECISION_OK);

        $dispatcher->setUser($user);

        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
    }

    public function testGetDic(): void
    {
        $dispatcher = $this->getMockBuilder(EasyCreditDispatcherController::class)
            ->disableOriginalConstructor()
            ->setMethods(['processEasyCreditDetails'])
            ->getMock();
        $dispatcher->expects($this->any())->method('processEasyCreditDetails')->willThrowException(new \Exception('test'));

        $this->assertEquals('payment', $dispatcher->getEasyCreditInstallmentDetails());
    }

    protected function getPaymentHash($oxUser, $oxBasket, $dic): string
    {
        return hash('sha256', json_encode($this->getCurrentInitializationData($oxUser, $oxBasket, $dic)));
    }

    protected function getCurrentInitializationData($oUser, $oBasket, $dic)
    {
        $requestBuilder = oxNew(EasyCreditInitializeRequestBuilder::class);

        $requestBuilder->setUser($oUser);
        $requestBuilder->setBasket($oBasket);
        $requestBuilder->setShippingAddress($this->getShippingAddress());

        $shopEdition = EasyCreditHelper::getShopSystem(Registry::getConfig()->getActiveShop());
        $requestBuilder->setShopEdition($shopEdition);

        $moduleVersion = EasyCreditHelper::getModuleVersion($dic);
        $requestBuilder->setModuleVersion($moduleVersion);

        $requestBuilder->setBaseLanguage(Registry::getLang()->getBaseLanguage());

        return $requestBuilder->getInitializationData();
    }

    protected function getShippingAddress()
    {
        /** @var $oOrder Order */
        $oOrder = oxNew(Order::class);
        return $oOrder->getDelAddressInfo();
    }
}
