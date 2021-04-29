<?php

namespace OxidProfessionalServices\EasyCredit\Core\Helper;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Price;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;

/**
 * Helper class with common used business methods.
 */
class EasyCreditHelper
{
    /**
     * Returns the price relevant for the example calculation.
     *
     * @param string $articleId
     * @param Basket $basket
     *
     * @return Price
     * @throws SystemComponentException
     */
    public static function getExampleCalculationPrice($articleId, $basket)
    {
        if ($articleId) {
            /** @var Article $article */
            $article = oxNew('oxarticle');
            if ($article->load($articleId)) {
                return $article->getPrice();
            }
        } else {
            return $basket->getPrice();
        }
    }

    /**
     * Checks if address fields "street" and "streetnr" have a packstation format.
     * This means either both fields are numeric or at least one of them contains the string "Packstation".
     *
     * @param $street
     * @param $streetnr
     *
     * @return bool
     */
    public static function hasPackstationFormat($street, $streetnr)
    {
        $packStationString = 'packstation';

        $street = strtolower(trim($street));
        $streetnr = strtolower(trim($streetnr));

        $numeric = (is_numeric($street) && is_numeric($streetnr));
        $ps = (strpos($street, $packStationString) !== false || strpos($streetnr, $packStationString) !== false);
        return (
            $numeric
            || $ps
        );
    }

    /**
     * Returns shop full edition
     *
     * @param $shop Shop
     *
     * @return string
     */
    public static function getShopSystem($shop)
    {
        $sEdition = $shop->oxshops__oxedition->value;
        $sFullEdition = "Community Edition";
        if ($sEdition == "PE") {
            $sFullEdition = "Professional Edition";
        }

        if ($sEdition == "EE") {
            $sFullEdition = "Enterprise Edition";
        }

        return $sFullEdition;
    }

    /**
     * Returns easyCredit module version
     *
     * @param EasyCreditDic $dic
     * @return string
     */
    public static function getModuleVersion(EasyCreditDic $dic)
    {
        /** @var $module Module */
        $module = oxNew('oxModule');
        if ($module->load($dic->getApiConfig()->getEasyCreditModuleId())) {
            return $module->getInfo('version');
        }
        return "";
    }
}