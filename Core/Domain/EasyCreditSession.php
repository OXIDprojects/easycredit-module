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

namespace OxidProfessionalServices\EasyCredit\Core\Domain;

use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;

/**
 * Class oxpsEasyCreditOxSession
 *
 * Enhancement for oxSession to handle easyCredit payment information (as a storage)
 */
class EasyCreditSession extends EasyCreditSession_parent
{
    const API_CONFIG_STORAGE = 'EasyCreditStorage';

    /**
     * Sets storage for easyCredit information
     *
     * @param $storage EasyCreditStorage
     */
    public function setStorage($storage)
    {
        if( empty($storage) ) {
            $this->deleteVariable(self::API_CONFIG_STORAGE);
        }
        else {
            $this->setVariable(self::API_CONFIG_STORAGE, serialize($storage));
        }
    }

    /**
     * Returns storage with easyCredit information
     *
     * @return \stdClass storage
     */
    public function getStorage()
    {
        /** @var $storage EasyCreditStorage */
        $storage = unserialize((string)$this->getVariable(self::API_CONFIG_STORAGE));
        if(!empty($storage) && $storage->hasExpired()) {
            $this->clearStorage();
            $storage = null;
        }
        return $storage;
    }

    /**
     * Clears storage for easyCredit information
     */
    public function clearStorage()
    {
        $this->setStorage(null);
    }
}