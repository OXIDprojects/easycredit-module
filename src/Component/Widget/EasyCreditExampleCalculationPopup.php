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

namespace OxidSolutionCatalysts\EasyCredit\Component\Widget;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDic;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Class EasyCreditExampleCalculationPopup
 *
 * @package OxidSolutionCatalysts\EasyCredit\Application\Component\Widget
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
        return "https://ratenkauf.easycredit-module.de/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf?shopKennung=$webshopId&bestellwert=$price";
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