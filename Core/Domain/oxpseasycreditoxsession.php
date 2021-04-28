<?php

/**
 * Class oxpsEasyCreditOxSession
 *
 * Enhancement for oxSession to handle easyCredit payment information (as a storage)
 */
class oxpsEasyCreditOxSession extends oxpsEasyCreditOxSession_parent
{
    const API_CONFIG_STORAGE = 'oxpsEasyCreditStorage';

    /**
     * Sets storage for easyCredit information
     *
     * @param $storage oxpsEasyCreditStorage
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
     * @return stdClass storage
     */
    public function getStorage()
    {
        /** @var $storage oxpsEasyCreditStorage */
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