<?php

namespace OxidProfessionalServices\EasyCredit\Application\Component\Widget;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;

class EasyCreditExampleCalculationPopup extends WidgetController
{
    protected $_sThisTemplate = 'oxpseasycredit_examplecalculation_popup.tpl';

    /** @var oxpsEasyCreditDic */
    private $dic;

    /**
     * @return oxpsEasyCreditDic
     * @throws oxSystemComponentException
     */
    protected function getDic()
    {
        if (!$this->dic) {
            $this->dic = oxpsEasyCreditDicFactory::getDic();
        }

        return $this->dic;
    }

    /**
     * Returns active basket
     *
     * @return oxBasket
     */
    public function getBasket()
    {
        return oxRegistry::getSession()->getBasket();
    }

    /**
     * Returns the price relevant for the example calculation.
     *
     * @return oxPrice
     * @throws oxSystemComponentException
     */
    public function getPrice()
    {
        return oxpsEasyCreditHelper::getExampleCalculationPrice($this->getViewParameter("articleId"), $this->getBasket());
    }

    public function getIFrameUrl()
    {
        $webshopId = $this->getWebshopId();
        $price = $this->getPrice()->getBruttoPrice();
        return "https://ratenkauf.easycredit.de/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf?shopKennung=$webshopId&bestellwert=$price";
    }

    protected function getWebshopId()
    {
        return $this->getDic()->getApiConfig()->getWebShopId();
    }
}