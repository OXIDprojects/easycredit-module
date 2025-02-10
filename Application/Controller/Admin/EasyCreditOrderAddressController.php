<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;

/**
 * Class oxpseasycreditorder_main
 *
 * Disables editing of addresses of easyCredit orders.
 */
class EasyCreditOrderAddressController extends EasyCreditOrderAddressController_parent
{
    /**
     * Overwrite parent render method to hook into.
     *
     * @return string
     */
    public function render()
    {
        $r = parent::render();

        $soxId = $this->_aViewData["oxid"] = $this->getEditObjectId();

        if ($soxId != "-1") {
            // load object
            $oOrder = oxNew(Order::class);
            $oOrder->load($soxId);

            $this->_aViewData["readonly"] =  ($oOrder->oxorder__oxpaymenttype->value === 'easycreditinstallment');
        }

        return $r;
    }
}
