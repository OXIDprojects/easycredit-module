<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Di;

use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;

/**
 * Interface DicSessionInterface
 *
 * Providing a session interface for http and unittest.
 */
interface EasyCreditDicSessionInterface
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
     * @param $storage EasyCreditStorage
     */
    public function setStorage($storage);

    /**
     * Returns storage for easyCredit information
     *
     * @return null|EasyCreditStorage
     */
    public function getStorage();

    /**
     * Clears storage for easyCredit information
     */
    public function clearStorage();
}
