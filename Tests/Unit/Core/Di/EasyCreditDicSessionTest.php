<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Core\Di;

use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicSession;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicSessionInterface;
use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;

/**
 * Class EasyCreditDicSessionTest
 */
class EasyCreditDicSessionTest extends UnitTestCase
{
    const GET_KEY   = 'GET_KEY_TEST';
    const GET_VALUE = 'GET_VALUE_TEST';

    const SET_KEY   = 'SET_KEY_TEST';
    const SET_VALUE = 'SET_VALUE_TEST';

    const DELETE_KEY   = 'DELETE_KEY_TEST';
    const DELETE_VALUE = 'DELETE_VALUE_TEST';

    const SESSION_ID = '1234';

    /** @var array */
    private $sessionStore;

    /** @var EasyCreditDicSessionInterface */
    private $dicSession;

    /** @var EasyCreditStorage */
    private $storage;

    /**
     * Set up test environment
     *
     * @throws SystemComponentException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->sessionStore                = [];
        $this->sessionStore[self::GET_KEY] = self::GET_VALUE;

        $oxSession = $this->getMock(
            Session::class,
            [
                'getVariable',
                'setVariable',
                'deleteVariable',
                'processUrl',
                'getId',
                'setStorage',
                'getStorage',
                'clearStorage'
            ]
        );

        $oxSession->expects($this->any())->method('getVariable')->willReturnCallback(
            function ($key) {
                return $this->sessionStore[$key];
            }
        );

        $oxSession->expects($this->any())->method('setVariable')->willReturnCallback(
            function ($key, $value) {
                $this->sessionStore[$key] = $value;
            }
        );

        $oxSession->expects($this->any())->method('deleteVariable')->willReturnCallback(
            function ($key) {
                unset($this->sessionStore[$key]);
            }
        );

        $oxSession->expects($this->any())->method('processUrl')->willReturnCallback(
            function ($url) {
                return $url . '-test';
            }
        );

        $oxSession->expects($this->any())->method('getId')->willReturn(self::SESSION_ID);

        $oxSession->expects($this->any())->method('setStorage')->willReturnCallback(
            function ($storage) {
                $this->storage = $storage;
            }
        );

        $oxSession->expects($this->any())->method('getStorage')->willReturnCallback(
            function () {
                return $this->storage;
            }
        );

        $oxSession->expects($this->any())->method('clearStorage')->willReturnCallback(
            function () {
                unset($this->storage);
            }
        );

        $this->dicSession = oxNew(EasyCreditDicSession::class, $oxSession);
    }

    /**
     * Tear down test environment
     *
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetNonExisting()
    {
        $this->assertNull($this->dicSession->get('xyz'));
    }

    public function testGetExisting()
    {
        $this->assertEquals(self::GET_VALUE, $this->dicSession->get(self::GET_KEY));
    }

    public function testSet()
    {
        $this->assertNull($this->dicSession->get(self::SET_KEY));
        $this->dicSession->set(self::SET_KEY, self::SET_VALUE);
        $this->assertEquals(self::SET_VALUE, $this->dicSession->get(self::SET_KEY));
    }

    public function testDelete()
    {
        $this->assertNull($this->dicSession->get(self::DELETE_KEY));
        $this->dicSession->set(self::DELETE_KEY, self::DELETE_VALUE);
        $this->assertEquals(self::DELETE_VALUE, $this->dicSession->get(self::DELETE_KEY));

        $this->dicSession->delete(self::DELETE_KEY);
        $this->assertNull($this->dicSession->get(self::DELETE_KEY));
    }

    public function testProcessUrl()
    {
        $url = 'https://test.url';
        $this->assertEquals($url . '-test', $this->dicSession->processUrl($url));
    }

    public function testGetId()
    {
        $this->assertEquals(self::SESSION_ID, $this->dicSession->getId());
    }

    public function testStorage()
    {
        $this->dicSession->clearStorage();
        $this->assertNull($this->dicSession->getStorage());

        $storage = new EasyCreditStorage('Vorgangskennung',
                                         'fachlicheVorgangskennung',
                                         'authorizationHash',
                                         '$authorizedAmount');
        $this->dicSession->setStorage($storage);
        $this->assertEquals($storage, $this->dicSession->getStorage());
    }
}
