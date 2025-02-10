<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Domain;

use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Price;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Basket manager
 */
class EasyCreditBasket extends EasyCreditBasket_parent
{
    /** @var bool flag to mark, if instalment costs are included in totals */
    private $excludeInstalmentsCosts = false;

    /**
     * Has basket interests included?
     *
     * @return bool
     */
    public function hasInterestsAmount()
    {
        return $this->getInterestsAmount();
    }

    /**
     * Returns easyCredit basket interests
     *
     * @return float|null
     */
    public function getInterestsAmount()
    {
        if (EasyCreditHelper::isEasyCreditInstallmentById($this->getPaymentId())) {

            $storage = $this->getDic()->getSession()->getStorage();
            if ($storage) {
                return $storage->getInterestAmount();
            }
        }
        return null;
    }

    /**
     * Returns the dic container.
     *
     * @return EasyCreditDic
     * @throws SystemComponentException
     * @codeCoverageIgnore
     */
    protected function getDic()
    {
        return EasyCreditDicFactory::getDic();
    }

    /**
     * Set costs. Overwritten to set easyCredit interests costs
     *
     * @param $sCostName
     * @param null $oPrice
     */
    public function setCost($sCostName, $oPrice = null)
    {
        parent::setCost($sCostName, $oPrice);
        if (!$this->excludeInstalmentsCosts && $sCostName == "oxpayment") {
            $this->setCost('easycredit_interests', $this->calcInterestsCost());
        }
    }

    /**
     * Returns current interests value as price object
     *
     * @return Price
     */
    public function calcInterestsCost()
    {

        /** @var $interestsPrice Price */
        $interestsPrice = oxNew(Price::class);
        if ($this->hasInterestsAmount()) {
            $interestsPrice->add($this->getInterestsAmount());
        }

        return $interestsPrice;
    }

    /**
     * Performs final sum calculation and rounding.
     */
    protected function _calcTotalPrice()
    {
        parent::_calcTotalPrice();

        if (isset($this->_aCosts['easycredit_interests'])) {
            $this->getPrice()->add($this->_aCosts['easycredit_interests']->getPrice());
        }
    }

    /**
     * Marks basket to calculate with or without instalments
     *
     * @param bool true = with instalments (default); fals = without instalments
     */
    public function setExcludeInstalmentsCosts($excludeInstalmentsCosts)
    {
        $this->excludeInstalmentsCosts = $excludeInstalmentsCosts;
    }
}
