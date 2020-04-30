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
 * Class oxpsEasyCreditDicSessionTest
 */
class oxpsEasyCreditDicSessionTest extends OxidTestCase
{
    const GET_KEY = 'GET_KEY_TEST';
    const GET_VALUE = 'GET_VALUE_TEST';

    const SET_KEY = 'SET_KEY_TEST';
    const SET_VALUE = 'SET_VALUE_TEST';

    const DELETE_KEY = 'DELETE_KEY_TEST';
    const DELETE_VALUE = 'DELETE_VALUE_TEST';

    const SESSION_ID = '1234';

    /** @var array */
    private $sessionStore;

    /** @var oxpsEasyCreditDicSessionInterface */
    private $dicSession;

    /** @var oxpsEasyCreditStorage */
    private $storage;

    /**
     * Set up test environment
     *
     * @return null
     * @throws oxSystemComponentException
     */
    public function setUp()
    {
        parent::setUp();

        $this->sessionStore = array();
        $this->sessionStore[self::GET_KEY] = self::GET_VALUE;

        $oxSession = $this->getMock(
            'oxSession',
            array('getVariable', 'setVariable', 'deleteVariable', 'processUrl', 'getId', 'setStorage', 'getStorage', 'clearStorage')
        );

        $oxSession->expects($this->any())->method('getVariable')->willReturnCallback(
            function($key) {
                return $this->sessionStore[$key];
            }
        );

        $oxSession->expects($this->any())->method('setVariable')->willReturnCallback(
            function($key, $value) {
                $this->sessionStore[$key] = $value;
            }
        );

        $oxSession->expects($this->any())->method('deleteVariable')->willReturnCallback(
            function($key) {
                unset($this->sessionStore[$key]);
            }
        );

        $oxSession->expects($this->any())->method('processUrl')->willReturnCallback(
            function($url) {
                return $url . '-test';
            }
        );

        $oxSession->expects($this->any())->method('getId')->willReturn(self::SESSION_ID);

        $oxSession->expects($this->any())->method('setStorage')->willReturnCallback(
            function($storage) {
                $this->storage = $storage;
            }
        );

        $oxSession->expects($this->any())->method('getStorage')->willReturnCallback(
            function() {
                return $this->storage;
            }
        );

        $oxSession->expects($this->any())->method('deleteStorage')->willReturnCallback(
            function() {
                unset($this->storage);
            }
        );

        $this->dicSession = oxNew('oxpsEasyCreditDicSession', $oxSession);
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

        $storage = new oxpsEasyCreditStorage('Vorgangskennung',
            'fachlicheVorgangskennung',
            'authorizationHash',
            '$authorizedAmount');
        $this->dicSession->setStorage($storage);
        $this->assertEquals($storage, $this->dicSession->getStorage());
    }
}
