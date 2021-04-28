<?php

namespace OxidProfessionalServices\EasyCredit\Core\Helper;

/**
 * Helper class with common used business methods.
 */
class EasyCreditHelper
{
    /**
     * Returns the price relevant for the example calculation.
     *
     * @param string $articleId
     * @param oxBasket $basket
     *
     * @return oxPrice
     * @throws oxSystemComponentException
     */
    public static function getExampleCalculationPrice($articleId, $basket)
    {
        if ($articleId) {
            /** @var oxArticle $article */
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
     * @param $shop oxShop
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
     * @param oxpsEasyCreditDic $dic
     * @return string
     */
    public static function getModuleVersion(oxpsEasyCreditDic $dic)
    {
        /** @var $module oxModule */
        $module = oxNew('oxModule');
        if ($module->load($dic->getApiConfig()->getEasyCreditModuleId())) {
            return $module->getInfo('version');
        }
        return "";
    }
}