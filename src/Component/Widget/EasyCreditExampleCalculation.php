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
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\Manufacturer;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Price;
use OxidSolutionCatalysts\EasyCredit\Core\Api\EasyCreditWebServiceClientFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDic;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditHelper;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditInitializeRequestBuilder;
use OxidSolutionCatalysts\EasyCredit\Service\EasyCreditModuleSettings;
use OxidSolutionCatalysts\EasyCredit\Traits\EasyCreditServiceContainer;
use stdClass;

/**
 * Class EasyCreditExampleCalculation
 *
 * @package OxidSolutionCatalysts\EasyCredit\Application\Component\Widget
 */
class EasyCreditExampleCalculation extends WidgetController
{
    use EasyCreditServiceContainer;

    protected $_sThisTemplate = '@osceasycredit/widget/easycredit/oxpseasycredit_examplecalculation';

    /** @var EasyCreditDic */
    private $dic;

    /** @var \stdClass */
    protected $exampleCalculation;

    /** @var Basket */
    protected $basket;

    /**
     * Return the monthly fee to pay for credit.
     *
     * @return string
     */
    public function getExampleCalculationRate()
    {
        if ($this->hasExampleCalculation()) {
            $test = $this->getExampleCalulation()->betragRate;
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
        return (bool)$this->getExampleCalulation();
    }

    /**     * Return example calculation.
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
     * @return false|\stdClass
     * @throws SystemComponentException
     */
    protected function getExampleCalculationResponse()
    {
        $price = $this->getPrice();
        $payment = oxNew(Payment::class);
        $payment->load('easycreditinstallment');

        if (
            !$price ||
            (int)$price->getBruttoPrice() < (int)$payment->getFieldData('oxfromamount') ||
            (int)$price->getBruttoPrice() > (int)$payment->getFieldData('oxtoamount')
        ) {
            return false;
        }

        try {
            /** @var EasyCreditDic $dic */
            $dic = $this->getDic();
            if (EasyCreditDicFactory::getDic()->getApiConfig()->getEasyCreditUseApiVersionV3()) {
                $apiConfig = $dic->getApiConfig();
                $articleData = $this->getBasketInfoV3();

                $webServiceClient = EasyCreditWebServiceClientFactory::getWebServiceClient(
                    EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V3_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN,
                    $dic,
                    [$apiConfig->getWebShopId()],
                    $articleData,
                    true
                );
                $response = $webServiceClient->executeJsonRequest('POST', $webServiceClient->getFunction(), $articleData);
                $plan = $this->getInstallmentPlanV3($response);
                return $plan;
            } else {
                $webServiceClient = EasyCreditWebServiceClientFactory::getWebServiceClient(
                    EasyCreditApiConfig::API_CONFIG_SERVICE_NAME_V1_MODELLRECHNUNG_GUENSTIGSTER_RATENPLAN,
                    $dic,
                    [],
                    [EasyCreditApiConfig::API_CONFIG_SERVICE_REST_ARGUMENT_FINANZIERUNGSBETRAG => $price->getBruttoPrice()]);
                return $webServiceClient->execute();
            }
        } catch (\Exception $ex) {
            $this->getDic()->getLogging()->log($ex->getMessage());
        }

        return false;
    }

    /**
     * Getter for config parameter.
     *
     * @return mixed
     */
    public function getUseOwnjQuery()
    {
        $moduleSettings = $this->getServiceFromContainer(EasyCreditModuleSettings::class);
        return $moduleSettings->getOxpsECExampleUseOwnjQueryUI();
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
        return (Registry::getRequest()->getRequestParameter('ajax') == 1);
    }

    /**
     * Returns "warenkorbinfos"
     *
     * @return array
     */
    public function getBasketInfoV3()
    {
        $basketInfo = [];

        $basketitemlist = $this->getBasket()->getBasketArticles();
        $basketContents = $this->getBasket()->getContents();
        if (empty($basketContents)) {
            return $basketInfo;
        }

        foreach ($basketContents as $basketindex => $basketitem) {
            $basketproduct = $basketitemlist[$basketindex];
            $basketInfo[] = $this->getBasketPositionInfoV3($basketitem, $basketproduct);
        }
        // $return = ['articles' => $basketInfo];
        $return = ['articles' => [['identifier' => 'Basket', 'price' => $this->getBasket()->getPrice()->getBruttoPrice()]]];
        return $return;
    }

    /**
     * Returns information about an certain basket position
     *
     * @param $basketitem BasketItem
     * @param $basketproduct
     *
     * @return array
     */
    protected function getBasketPositionInfoV3($basketitem, $basketproduct)
    {
        $price = "";
        $unitPrice = $basketitem->getUnitPrice();
        if ($unitPrice) {
            $price = $unitPrice->getPrice();
        }
            return [
                    "identifier" => $basketitem->getTitle(),
                    "price" => $price,
            ];
    }
    
    protected function getInstallmentPlanV3($response) {
        $cheapestPlan = $response->installmentPlans[0]->plans[array_key_last($response->installmentPlans[0]->plans)];
        $return = new StdClass();
        $return->anzahlRaten = $cheapestPlan->numberOfInstallments;
        $return->betragRate = $cheapestPlan->installment;
        $return->gesamtsumme = $cheapestPlan->totalValue;
        return $return;
    }
    
    public function getEasyCreditWebShopId() {
        return $this->getDic()->getApiConfig()->getWebShopId();
    }

    public function getEasyCreditUseApiVersionV3() {
        return $this->getDic()->getApiConfig()->getEasyCreditUseApiVersionV3();
    }

    /**
     * Returns the price relevant for the example calculation.
     *
     * @return float
     * @throws SystemComponentException
     */
    public function getPriceV3()
    {
        return EasyCreditHelper::getExampleCalculationPrice($this->getViewParameter("articleId"), $this->getBasket())->getBruttoPrice();
    }
    
    
}
