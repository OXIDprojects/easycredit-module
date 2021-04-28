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
 * Class DicSession
 *
 * Pipe get, set and delete to underlying oxSession.
 */
class oxpsEasyCreditDicSession implements oxpsEasyCreditDicSessionInterface
{
    /** @var oxSession */
    private $session;

    /**
     * DicSession constructor.
     *
     * @param oxSession $session
     */
    public function __construct(oxSession $session)
    {
        $this->session = $session;
    }

    /**
     * Returns the value for the key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->session->getVariable($key);
    }

    /**
     * Sets the value for the key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->session->setVariable($key, $value);
    }

    /**
     * Deletes the key/value pair.
     *
     * @param string $key
     */
    public function delete($key)
    {
        $this->session->deleteVariable($key);
    }

    /**
     * Appends url with session ID, but only if oxSession::_isSidNeeded() returns true
     * Direct usage of this method to retrieve end url result is discouraged - instead
     * see oxUtilsUrl::processUrl
     *
     * @param string $sUrl url to append with sid
     *
     * @see oxUtilsUrl::processUrl
     *
     * @return string
     */
    public function processUrl($sUrl)
    {
        return $this->session->processUrl($sUrl);
    }

    /**
     * Returns session ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->session->getId();
    }

    /**
     * Sets storage for easyCredit information
     *
     * @param $storage oxpsEasyCreditStorage
     */
    public function setStorage($storage)
    {
        $this->session->setStorage($storage);
    }

    /**
     * Returns storage for easyCredit information
     *
     * @return null|oxpsEasyCreditStorage
     */
    public function getStorage() {

        return $this->session->getStorage();
    }

    /**
     * Clears storage for easyCredit information
     */
    public function clearStorage() {

        $this->session->clearStorage();
    }
}