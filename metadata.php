<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       easycredit-module
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

/**
 * Metadata version
 */

use OxidEsales\Eshop\Application\Controller\Admin\OrderAddress;
use OxidEsales\Eshop\Application\Controller\Admin\OrderArticle;
use OxidEsales\Eshop\Application\Controller\Admin\OrderList;
use OxidEsales\Eshop\Application\Controller\Admin\OrderOverview;
use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin\EasyCreditOrderAddressController;
use OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin\EasyCreditOrderArticleController;
use OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin\EasyCreditOrderListController;
use OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin\EasyCreditOrderOverviewController;
use OxidSolutionCatalysts\EasyCredit\Application\Controller\EasyCreditOrderController;
use OxidSolutionCatalysts\EasyCredit\Application\Controller\EasyCreditPaymentController;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditBasket;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditOrder;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditPayment;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditSession;

$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => 'osceasycredit',
    'title'       => [
        'de' => 'easyCredit-Ratenkauf für OXID',
        'en' => 'easyCredit-Ratenkauf for OXID',
    ],
    'description' => [
        'de' => 'easyCredit-Ratenkauf für Einkäufe in OXID nutzen',
        'en' => 'Use easyCredit-Ratenkauf for purchases in OXID',
    ],
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '4.0.0',
    'author'      => 'OXID Solution Catalysts',
    'url'         => 'https://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'controllers' => [
        'EasyCreditDispatcher'              => \OxidSolutionCatalysts\EasyCredit\Application\Controller\EasyCreditDispatcherController::class,
        # Admin
        'EasyCreditOrderEasyCredit'         => \OxidSolutionCatalysts\EasyCredit\Application\Controller\Admin\EasyCreditOrderEasyCreditController::class,
        # Widgets
        'easycreditexamplecalculation'      => \OxidSolutionCatalysts\EasyCredit\Application\Component\Widget\EasyCreditExampleCalculation::class,
        'easycreditexamplecalculationpopup' => \OxidSolutionCatalysts\EasyCredit\Application\Component\Widget\EasyCreditExampleCalculationPopup::class,
    ],
    'extend'      => array(
        # extended controller
        PaymentController::class   => EasyCreditPaymentController::class,
        OrderController::class     => EasyCreditOrderController::class,

        # Extended admin controller
        OrderAddress::class  => EasyCreditOrderAddressController::class,
        OrderArticle::class  => EasyCreditOrderArticleController::class,
        OrderOverview::class => EasyCreditOrderOverviewController::class,
        OrderList::class     => EasyCreditOrderListController::class,

        # Extending core classes
        Session::class                                                      => EasyCreditSession::class,
        \OxidEsales\Eshop\Application\Model\Payment::class                  => EasyCreditPayment::class,
        \OxidEsales\Eshop\Application\Model\Basket::class                   => EasyCreditBasket::class,
        \OxidEsales\Eshop\Application\Model\Order::class                    => EasyCreditOrder::class
    ),
    'templates'   => [
        'page/checkout/inc/payment_easycreditinstallment.tpl' => 'oxps/easycredit-module/Application/views/page/checkout/inc/oxpseasycredit_payment_easycreditinstallment.tpl',
        'oxpseasycredit_examplecalculation.tpl'               => 'oxps/easycredit-module/Application/views/widgets/oxpseasycredit_examplecalculation.tpl',
        'oxpseasycredit_examplecalculation_popup.tpl'         => 'oxps/easycredit-module/Application/views/widgets/oxpseasycredit_examplecalculation_popup.tpl',
        'oxpseasycredit_order_easycredit.tpl'                 => 'oxps/easycredit-module/Application/views/admin/tpl/oxpseasycredit_order_easycredit.tpl',
        'easycredit_overview.tpl'                             => 'oxps/easycredit-module/Application/views/admin/tpl/easycredit_overview.tpl',
        'easycredit_overview_list.tpl'                        => 'oxps/easycredit-module/Application/views/admin/tpl/easycredit_overview_list.tpl',
        'easycredit_overview_main.tpl'                        => 'oxps/easycredit-module/Application/views/admin/tpl/easycredit_overview_main.tpl',
    ],
    'blocks'      => [
        [
            'template' => 'page/checkout/payment.tpl',
            'block'    => 'select_payment',
            'file'     => 'Application/views/blocks/oxpseasycreditselect_payment.tpl',
        ],
        [
            'template' => 'page/details/inc/productmain.tpl',
            'block'    => 'details_productmain_price_value',
            'file'     => 'Application/views/blocks/oxpseasycreditselect_productmain.tpl',
        ],
        [
            'template' => 'page/checkout/basket.tpl',
            'block'    => 'checkout_basket_next_step_bottom',
            'file'     => 'Application/views/blocks/oxpseasycreditselect_basket.tpl',
        ],
        [
            'template' => 'widget/header/minibasket.tpl',
            'block'    => 'dd_layout_page_header_icon_menu_minibasket_list',
            'file'     => 'Application/views/blocks/oxpseasycreditselect_minibasket.tpl',
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block'    => 'shippingAndPayment',
            'file'     => 'Application/views/blocks/oxpseasycredit_order_payment.tpl',
        ],
        [
            'template' => 'page/checkout/inc/basketcontents.tpl',
            'block'    => 'checkout_basketcontents_delcosts',
            'file'     => 'Application/views/blocks/oxpseasycredit_basketcontents_interests.tpl',
        ],
        [
            'template' => 'email/html/order_owner.tpl',
            'block'    => 'email_html_order_owner_grandtotal',
            'file'     => 'Application/views/blocks/email/html/oxpseasycredit_order_owner_grandtotal.tpl',
        ],
        [
            'template' => 'email/html/order_owner.tpl',
            'block'    => 'email_html_order_owner_paymentinfo',
            'file'     => 'Application/views/blocks/email/html/oxpseasycredit_order_owner_paymentinfo.tpl',
        ],
        [
            'template' => 'email/html/order_cust.tpl',
            'block'    => 'email_html_order_cust_grandtotal',
            'file'     => 'Application/views/blocks/email/html/oxpseasycredit_order_cust_grandtotal.tpl',
        ],
        [
            'template' => 'email/html/order_cust.tpl',
            'block'    => 'email_html_order_cust_paymentinfo_top',
            'file'     => 'Application/views/blocks/email/html/oxpseasycredit_order_cust_paymentinfo.tpl',
        ],
        [
            'template' => 'email/plain/order_owner.tpl',
            'block'    => 'email_plain_order_ownergrandtotal',
            'file'     => 'Application/views/blocks/email/plain/oxpseasycredit_order_owner_grandtotal.tpl',
        ],
        [
            'template' => 'email/plain/order_owner.tpl',
            'block'    => 'email_plain_order_ownerpaymentinfo',
            'file'     => 'Application/views/blocks/email/plain/oxpseasycredit_order_owner_paymentinfo.tpl',
        ],
        [
            'template' => 'email/plain/order_cust.tpl',
            'block'    => 'email_plain_order_cust_grandtotal',
            'file'     => 'Application/views/blocks/email/plain/oxpseasycredit_order_cust_grandtotal.tpl',
        ],
        [
            'template' => 'email/plain/order_cust.tpl',
            'block'    => 'email_plain_order_cust_paymentinfo',
            'file'     => 'Application/views/blocks/email/plain/oxpseasycredit_order_cust_paymentinfo.tpl',
        ],
        [
            'template' => 'order_overview.tpl',
            'block'    => 'admin_order_overview_total',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_overview_total.tpl',
        ],
        [
            'template' => 'order_overview.tpl',
            'block'    => 'admin_order_overview_send_form',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_overview_ec_delivery_state.tpl',
        ],
        [
            'template' => 'order_article.tpl',
            'block'    => 'admin_order_article_total',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_article_total.tpl',
        ],
        [
            'template' => 'order_article.tpl',
            'block'    => 'admin_order_article_listitem',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_article_listitem.tpl',
        ],

        [
            'template' => 'order_list.tpl',
            'block'    => 'admin_order_list_colgroup',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_list_colgroups.tpl',
        ],
        [
            'template' => 'order_list.tpl',
            'block'    => 'admin_order_list_filter',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_list_filter.tpl',
        ],
        [
            'template' => 'order_list.tpl',
            'block'    => 'admin_order_list_sorting',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_list_sorting.tpl',
        ],
        [
            'template' => 'order_list.tpl',
            'block'    => 'admin_order_list_item',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_list_item.tpl',
        ],

        [
            'template' => 'order_main.tpl',
            'block'    => 'admin_order_main_form_details',
            'file'     => 'Application/views/blocks/admin/oxpseasycredit_order_main_form_details.tpl',
        ]
    ],
    'settings'    => [
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECBaseUrl',
            'type'  => 'str',
            'value' => 'https://ratenkauf.easycredit-module.de/ratenkauf-ws/rest',
        ],
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECDealerInterfaceUrl',
            'type'  => 'str',
            'value' => 'https://app.easycredit-module.de/ratenkauf/transaktionsverwaltung-ws/rest',
        ],
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECWebshopId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECWebshopToken',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'EasyCreditCheckout',
            'name'  => 'oxpsECCheckoutValidConfirm',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'EasyCreditExampleCalculation',
            'name'  => 'oxpsECExampleCalcArticle',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'EasyCreditExampleCalculation',
            'name'  => 'oxpsECExampleCalcBasket',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'EasyCreditExampleCalculation',
            'name'  => 'oxpsECExampleCalcMinibasket',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'EasyCreditExampleCalculation',
            'name'  => 'oxpsECExampleUseOwnjQueryUI',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'EasyCreditLogging',
            'name'  => 'oxpsECLogging',
            'type'  => 'bool',
            'value' => false,
        ]
    ],
    'events'      => [
        'onActivate'   => '\OxidSolutionCatalysts\EasyCredit\Core\Events::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\EasyCredit\Core\Events::onDeactivate',
    ],
];
