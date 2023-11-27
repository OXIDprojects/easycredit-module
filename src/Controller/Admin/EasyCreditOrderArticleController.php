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

namespace OxidSolutionCatalysts\EasyCredit\Controller\Admin;

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
        // no changes for order with easycredit-module-payment
        $oOrder = $this->getEditObject();
        if ($oOrder->oxorder__oxpaymenttype->value == "easycreditinstallment") {
            return null;
        }
        return parent::getSearchProduct();
    }
}
