<?php

namespace OxidProfessionalServices\EasyCredit\Application\Component\Widget;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;

class EasyCreditExampleCalculation extends WidgetController
{
    protected $_sThisTemplate = 'oxpseasycredit_examplecalculation.tpl';

    /** @var oxpsEasyCreditDic */
    private $dic;

    /** @var stdClass */
    protected $exampleCalculation;

    /** @var oxBasket */
    protected $basket;

    public function getExampleCalculationRate()
    {
        if ($this->hasExampleCalculation()) {
            return oxRegistry::getLang()->formatCurrency($this->getExampleCalulation()->betragRate);
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
     * Returns the price relevant for the example calculation.
     *
     * @return oxPrice
     * @throws oxSystemComponentException
     */
    protected function getPrice()
    {
        return oxpsEasyCreditHelper::getExampleCalculationPrice($this->getViewParameter("articleId"), $this->getBasket());
    }

    /**
     * Returns active basket
     *
     * @return oxBasket
     */
    protected function getBasket()
    {
        return oxRegistry::getSession()->getBasket();
    }

    protected function getExampleCalculationResponse()
    {
        $price = $this->getPrice();
        if (!$price) {
            return false;
        }

        try {
            /** @var oxpsEasyCreditDic $dic */
            $dic = $this->getDic();

            $webServiceClient = oxpsEasyCreditWebServiceClientFactory::getWebServiceClient(
                oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN,
                $dic,
                array(),
                array(oxpsEasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_FINANZIERUNGSBETRAG => $price->getBruttoPrice()));
            return $webServiceClient->execute();
        } catch (Exception $ex) {
            $this->getDic()->getLogging()->log($ex->getMessage());
        }
    }
    public function getAjaxUrl()
    {
        $sURL = oxRegistry::getConfig()->getConfigParam('sShopURL');
        $articleId = $this->getViewParameter("articleId");
        return $sURL . 'index.php?cl=oxpseasycreditexamplecalculation' . ($articleId ? '&articleId=' . $articleId : '') . '&placeholderId=' . $this->getViewParameter("placeholderId") . '&ajax=1';
    }

    public function getPopupAjaxUrl()
    {
        $sURL = oxRegistry::getConfig()->getConfigParam('sShopURL');
        $articleId = $this->getViewParameter("articleId");
        return $sURL . 'index.php?cl=oxpseasycreditexamplecalculationpopup' . ($articleId ? '&articleId=' . $articleId : '') . '&ajax=1';
    }

    public function isAjax()
    {
        return (oxRegistry::getConfig()->getRequestParameter('ajax') == 1);
    }
}