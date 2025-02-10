<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Domain;

use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Class oxpsEasyCreditOxPayment
 */
class EasyCreditPayment extends EasyCreditPayment_parent
{
    /**
     * Returns true if payment is ratenkauf by easyCredit
     *
     * @return bool
     */
    public function isEasyCreditInstallment()
    {
        return EasyCreditHelper::isEasyCreditInstallmentById($this->getId());
    }
}
