<?php

namespace OxidProfessionalServices\EasyCredit\Core\Di;


use OxidEsales\Eshop\Core\Exception\StandardException;

class EasyCreditConfigException extends StandardException
{
    /**
     * oxpsEasyCreditConfigException constructor.
     *
     * @param string $sMessage
     * @param int $iCode
     */
    public function __construct($sMessage = "not set", $iCode = 0)
    {
        parent::__construct($sMessage, $iCode);
    }
}