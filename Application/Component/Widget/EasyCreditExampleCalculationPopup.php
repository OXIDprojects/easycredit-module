<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Application\Component\Widget;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Class EasyCreditExampleCalculationPopup
 *
 * @package OxidProfessionalServices\EasyCredit\Application\Component\Widget
 */
class EasyCreditExampleCalculationPopup extends WidgetController
{
    protected $_sThisTemplate = 'oxpseasycredit_examplecalculation_popup.tpl';

    /** @var EasyCreditDic */
    private $dic;

    /**
     * @return EasyCreditDic
     * @throws SystemComponentException
     */
    protected function getDic()
    {
        if (!$this->dic) {
            $this->dic = EasyCreditDicFactory::getDic();
        }

        return $this->dic;
    }

    /**
     * Returns active basket
     *
     * @return Basket
     */
    public function getBasket()
    {
        return Registry::getSession()->getBasket();
    }

    /**
     * Returns the price relevant for the example calculation.
     *
     * @return Price
     * @throws SystemComponentException
     */
    public function getPrice()
    {
        return EasyCreditHelper::getExampleCalculationPrice($this->getViewParameter("articleId"), $this->getBasket());
    }

    /**
     * Getter for EC Ratenkauf frame url
     *
     * @return string
     * @throws SystemComponentException
     */
    public function getIFrameUrl()
    {
        $webshopId = $this->getWebshopId();
        $price = $this->getPrice()->getBruttoPrice();
        return "https://ratenkauf.easycredit.de/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf?shopKennung=$webshopId&bestellwert=$price";
    }

    /**
     * Getter for webshop id.
     *
     * @return mixed
     * @throws SystemComponentException
     */
    protected function getWebshopId()
    {
        return $this->getDic()->getApiConfig()->getWebShopId();
    }
}
