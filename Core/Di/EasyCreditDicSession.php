<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2021
 */

namespace OxidProfessionalServices\EasyCredit\Core\Di;

use OxidEsales\Eshop\Core\Session;
use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;

/**
 * Class DicSession
 *
 * Pipe get, set and delete to underlying Session.
 */
class EasyCreditDicSession implements EasyCreditDicSessionInterface
{
    /** @var Session */
    private $session;

    /**
     * DicSession constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
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
     * Appends url with session ID, but only if Session::_isSidNeeded() returns true
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
     * @param $storage EasyCreditStorage
     */
    public function setStorage($storage)
    {
        $this->session->setStorage($storage);
    }

    /**
     * Returns storage for easyCredit information
     *
     * @return null|EasyCreditStorage
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