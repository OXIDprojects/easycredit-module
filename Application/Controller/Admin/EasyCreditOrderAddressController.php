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

        if ($soxId != "-1" && isset($soxId)) {
            // load object
            $oOrder = oxNew(Order::class);
            $oOrder->load($soxId);

            $this->_aViewData["readonly"] =  ($oOrder->oxorder__oxpaymenttype->value === 'easycreditinstallment');
        }

        return $r;
    }
}
