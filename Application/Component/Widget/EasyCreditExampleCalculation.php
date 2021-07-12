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

namespace OxidProfessionalServices\EasyCredit\Application\Component\Widget;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Price;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Class EasyCreditExampleCalculation
 *
 * @package OxidProfessionalServices\EasyCredit\Application\Component\Widget
 */
class EasyCreditExampleCalculation extends WidgetController
{
    protected $_sThisTemplate = 'oxpseasycredit_examplecalculation.tpl';

    /** @var EasyCreditDic */
    private $dic;

    /** @var \stdClass */
    protected $exampleCalculation;

    /** @var Basket */
    protected $basket;

    public function getExampleCalculationRate()
    {
        if ($this->hasExampleCalculation()) {
            return Registry::getLang()->formatCurrency($this->getExampleCalulation()->betragRate);
        }
    }

    public function hasExampleCalculation()
    {
        return (bool)$this->getExampleCalulation();
    }

    protected function getExampleCalulation()
    {
        if (!$this->exampleCalculation) {
            $response = $this->getExampleCalculationResponse();
            if ($response) {
                $this->exampleCalculation = $response;
            }
        }

        return $this->exampleCalculation;
    }

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
     * Returns the price relevant for the example calculation.
     *
     * @return Price
     * @throws SystemComponentException
     */
    protected function getPrice()
    {
        return EasyCreditHelper::getExampleCalculationPrice($this->getViewParameter("articleId"), $this->getBasket());
    }

    /**
     * Returns active basket
     *
     * @return Basket
     */
    protected function getBasket()
    {
        return Registry::getSession()->getBasket();
    }

    protected function getExampleCalculationResponse()
    {
        $price = $this->getPrice();
        if (!$price) {
            return false;
        }

        try {
            /** @var EasyCreditDic $dic */
            $dic = $this->getDic();

            $webServiceClient = EasyCreditWebServiceClientFactory::getWebServiceClient(
                EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN,
                $dic,
                array(),
                array(EasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_FINANZIERUNGSBETRAG => $price->getBruttoPrice()));
            return $webServiceClient->execute();
        } catch (\Exception $ex) {
            $this->getDic()->getLogging()->log($ex->getMessage());
        }
    }

    public function getUseOwnjQueryUI()
    {
        return Registry::getConfig()->getConfigParam('oxpsECExampleUseOwnjQueryUI');
    }

    public function getAjaxUrl()
    {
        $sURL = Registry::getConfig()->getConfigParam('sShopURL');
        $articleId = $this->getViewParameter("articleId");
        return $sURL . 'index.php?cl=easycreditexamplecalculation' . ($articleId ? '&articleId=' . $articleId : '') . '&placeholderId=' . $this->getViewParameter("placeholderId") . '&ajax=1';
    }

    public function getPopupAjaxUrl()
    {
        $sURL = Registry::getConfig()->getConfigParam('sShopURL');
        $articleId = $this->getViewParameter("articleId");
        return $sURL . 'index.php?cl=easycreditexamplecalculationpopup' . ($articleId ? '&articleId=' . $articleId : '') . '&ajax=1';
    }

    public function isAjax()
    {
        return (Registry::getConfig()->getRequestParameter('ajax') == 1);
    }
}