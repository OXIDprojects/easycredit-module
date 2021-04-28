<?php

namespace OxidProfessionalServices\EasyCredit\Core\Api;

use OxidEsales\Eshop\Core\Exception\StandardException;

class EasyCreditValidationException extends StandardException
{
    /**
     * EasyCreditValidationException constructor.
     *
     * @param string $sMessage
     * @param int $iCode
     */
    public function __construct($sMessage = "not set", $iCode = 0)
    {
        parent::__construct($sMessage, $iCode);
    }
}