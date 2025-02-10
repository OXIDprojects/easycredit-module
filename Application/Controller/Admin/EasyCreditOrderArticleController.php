<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;

/**
 * Class EasyCreditOrderArticleController
 *
 * Disables editing of addresses of easyCredit orders.
 */
class EasyCreditOrderArticleController extends EasyCreditOrderArticleController_parent
{
    /**
     * If possible returns searched/found oxarticle object
     *
     * @return \OxidEsales\Eshop\Application\Model\Article | false
     */
    public function getSearchProduct()
    {
        // no changes for order with easycredit-payment
        $oOrder = $this->getEditObject();
        if ($oOrder->oxorder__oxpaymenttype->value == "easycreditinstallment") {
            return null;
        }
        return parent::getSearchProduct();
    }
}
