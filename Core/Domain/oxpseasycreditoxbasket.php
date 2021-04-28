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
 * @copyright (C) OXID eSales AG 2003-2018
 * @version   OXID eShop EE
 */

/**
 * Basket manager
 */
class oxpsEasyCreditOxBasket extends oxpsEasyCreditOxBasket_parent
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
        if ( oxpsEasyCreditOxPayment::isEasyCreditInstallmentById($this->getPaymentId())) {

            $storage = $this->getDic()->getSession()->getStorage();
            if( $storage ) {
                return $storage->getInterestAmount();
            }
        }
        return null;
    }

    /**
     * Returns the dic container.
     *
     * @return oxpsEasyCreditDic
     * @throws oxSystemComponentException
     * @codeCoverageIgnore
     */
    protected function getDic()
    {
        return oxpsEasyCreditDicFactory::getDic();
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
        if(!$this->excludeInstalmentsCosts && $sCostName == "oxpayment") {
            $this->setCost('easycredit_interests', $this->calcInterestsCost());
        }
    }

    /**
     * Returns current interests value as price object
     *
     * @return oxPrice
     */
    public function calcInterestsCost() {

        /** @var $interestsPrice oxPrice */
        $interestsPrice = oxNew('oxPrice');
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
