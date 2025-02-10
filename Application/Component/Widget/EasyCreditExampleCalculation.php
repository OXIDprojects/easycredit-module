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
use OxidEsales\Eshop\Application\Model\Payment;
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

    /**
     * Return the monthly fee to pay for credit.
     *
     * @return string|void
     */
    public function getExampleCalculationRate()
    {
        if ($this->hasExampleCalculation()) {
            return Registry::getLang()->formatCurrency($this->getExampleCalulation()->betragRate);
        }
    }

    /**
     * Is there an example calculation?
     *
     * @return bool
     */
    public function hasExampleCalculation()
    {
        return (bool) $this->getExampleCalulation();
    }

    /**
     * Return example calculation.
     *
     * @return \stdClass
     */
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
     * Return the DIC
     *
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

    /**
     * Load example calculation from ec service.
     *
     * @return false|\stdClass|void
     * @throws SystemComponentException
     */
    protected function getExampleCalculationResponse()
    {
        $price = $this->getPrice();
        $payment = oxNew(Payment::class);
        $payment->load('easycreditinstallment');

        if (
            !$price ||
            (int) $price->getBruttoPrice() < (int) $payment->getFieldData('oxfromamount') ||
            (int) $price->getBruttoPrice() > (int) $payment->getFieldData('oxtoamount')
        ) {
            return false;
        }

        try {
            /** @var EasyCreditDic $dic */
            $dic = $this->getDic();

            $webServiceClient = EasyCreditWebServiceClientFactory::getWebServiceClient(
                EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN,
                $dic,
                [],
                [EasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_FINANZIERUNGSBETRAG => $price->getBruttoPrice()]
            );
            return $webServiceClient->execute();
        } catch (\Exception $ex) {
            $this->getDic()->getLogging()->log($ex->getMessage());
        }
    }

    /**
     * Getter for config parameter.
     *
     * @return mixed
     */
    public function getUseOwnjQueryUI()
    {
        return Registry::getConfig()->getConfigParam('oxpsECExampleUseOwnjQueryUI');
    }

    /**
     * Getter for ajax request url.
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        $sURL = Registry::getConfig()->getConfigParam('sShopURL');
        $articleId = $this->getViewParameter("articleId");
        return $sURL . 'index.php?cl=easycreditexamplecalculation' . ($articleId ? '&articleId=' . $articleId : '') . '&placeholderId=' . $this->getViewParameter("placeholderId") . '&ajax=1';
    }

    /**
     * Getter for ajax popup url.
     *
     * @return string
     */
    public function getPopupAjaxUrl()
    {
        $sURL = Registry::getConfig()->getConfigParam('sShopURL');
        $articleId = $this->getViewParameter("articleId");
        return $sURL . 'index.php?cl=easycreditexamplecalculationpopup' . ($articleId ? '&articleId=' . $articleId : '') . '&ajax=1';
    }

    /**
     * Getter for request parameter to decide if it is an ajax request.
     *
     * @return bool
     */
    public function isAjax()
    {
        return (Registry::getConfig()->getRequestParameter('ajax') == 1);
    }
}
