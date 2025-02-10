<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Exception;

use OxidEsales\Eshop\Core\Exception\StandardException;

/**
 * Exception base class for easyCredit
 */
class EasyCreditException extends StandardException
{
    /**
     * EasyCreditException constructor.
     *
     * @param string $sMessage
     * @param int    $iCode
     */
    public function __construct($sMessage, $iCode = 0)
    {
        parent::__construct($sMessage, $iCode);
    }
}
