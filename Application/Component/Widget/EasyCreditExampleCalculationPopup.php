<?php

namespace OxidProfessionalServices\EasyCredit\Application\Component\Widget;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;

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