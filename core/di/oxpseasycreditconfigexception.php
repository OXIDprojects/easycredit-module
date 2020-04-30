<?php

class oxpsEasyCreditConfigException extends oxException
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