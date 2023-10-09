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
namespace OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Core\Registry;

/**
 * Class EasyCreditOrderListController
 *
 * @package OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin
 */
class EasyCreditOrderListController extends EasyCreditOrderListController_parent
{
    /**
     * Render method
     *
     * @return string
     */
    public function render()
    {
        $template = parent::render();
        $this->addTplParam('ecorders', Registry::getRequest()->getRequestParameter('ecorders'));

        return $template;
    }

    /**
     * Prepares SQL where query according SQL condition array and attaches it to SQL end.
     * For each search value if german umlauts exist, adds them
     * and replaced by spec. char to query
     *
     * @param array  $whereQuery SQL condition array
     * @param string $fullQuery  SQL query string
     *
     * @return string
     * @deprecated underscore prefix violates PSR12, will be renamed to "prepareWhereQuery" in next major
     */
    protected function _prepareWhereQuery($whereQuery, $fullQuery) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $query = parent::_prepareWhereQuery($whereQuery, $fullQuery);
        $orders = Registry::getRequest()->getRequestParameter('ecorders');
        switch ($orders) {
            case 'only':
                $query .= " and ( `$this->_sListClass`.`ecredfunctionalid` IS NOT NULL ) ";
                break;
            case 'not':
                $query .= " and ( `$this->_sListClass`.`ecredfunctionalid` IS NULL ) ";
                break;
            default:
        }

        return $query;
    }
}