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
 * Interface DicSessionInterface
 *
 * Providing a session interface for http and unittest.
 */
interface oxpsEasyCreditDicSessionInterface
{
    /**
     * Returns the value for the key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Sets the value for the key.
     *
     * @param string $key
     * @param mixed $value
     * @return string
     */
    public function set($key, $value);

    /**
     * Deletes the key/value pair.
     *
     * @param string $key
     */
    public function delete($key);

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
    public function processUrl($sUrl);

    /**
     * Returns session ID
     *
     * @return string
     */
    public function getId();

    /**
     * Sets storage for easyCredit information
     *
     * @param $storage oxpsEasyCreditStorage
     */
    public function setStorage($storage);

    /**
     * Returns storage for easyCredit information
     *
     * @return null|oxpsEasyCreditStorage
     */
    public function getStorage();

    /**
     * Clears storage for easyCredit information
     */
    public function clearStorage();
}