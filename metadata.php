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

use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditBasket;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditOrder;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditPayment;
use OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditSession;
use OxidSolutionCatalysts\EasyCredit\Core\EasyCreditViewConfig;

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
    'thumbnail'   => 'img/picture.png',
    'version'     => '4.0.0',
    'author'      => 'OXID Solution Catalysts',
    'url'         => 'https://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'controllers' => [
        'EasyCreditDispatcher'              => \OxidSolutionCatalysts\EasyCredit\Controller\EasyCreditDispatcherController::class,
        # Admin
        'EasyCreditOrderEasyCredit'         => \OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderEasyCreditController::class,
        # Widgets
        'easycreditexamplecalculation'      => \OxidSolutionCatalysts\EasyCredit\Component\Widget\EasyCreditExampleCalculation::class,
        'easycreditexamplecalculationpopup' => \OxidSolutionCatalysts\EasyCredit\Component\Widget\EasyCreditExampleCalculationPopup::class,
    ],
    'extend'      => [
        # extended controller
        OxidEsales\Eshop\Application\Controller\PaymentController::class => OxidSolutionCatalysts\EasyCredit\Controller\EasyCreditPaymentController::class,
        OxidEsales\Eshop\Application\Controller\OrderController::class => OxidSolutionCatalysts\EasyCredit\Controller\EasyCreditOrderController::class,

        # Extended admin controller
        OxidEsales\Eshop\Application\Controller\Admin\OrderAddress::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderAddressController::class,
        OxidEsales\Eshop\Application\Controller\Admin\OrderArticle::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderArticleController::class,
        OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderOverviewController::class,
        OxidEsales\Eshop\Application\Controller\Admin\OrderList::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderListController::class,

        # Extending core classes
        Session::class                                                      => EasyCreditSession::class,
        \OxidEsales\Eshop\Application\Model\Payment::class                  => EasyCreditPayment::class,
        \OxidEsales\Eshop\Application\Model\Basket::class                   => EasyCreditBasket::class,
        \OxidEsales\Eshop\Application\Model\Order::class                    => EasyCreditOrder::class,
        \OxidEsales\Eshop\Core\ViewConfig::class                            => EasyCreditViewConfig::class
    ],
    'templates'   => [
        'page/checkout/inc/payment_easycreditinstallment.tpl' => 'views/page/checkout/inc/oxpseasycredit_payment_easycreditinstallment.tpl',
        'widget/easycredit/oxpseasycredit_examplecalculation' => 'views/widgets/oxpseasycredit_examplecalculation.tpl',
        'widget/easycredit/oxpseasycredit_examplecalculation_popup' => 'views/widgets/oxpseasycredit_examplecalculation_popup.tpl',
        'oxpseasycredit_order_easycredit.tpl'                 => 'views/admin/tpl/oxpseasycredit_order_easycredit.tpl',
        'easycredit_overview.tpl'                             => 'views/admin/tpl/easycredit_overview.tpl',
        'easycredit_overview_list.tpl'                        => 'views/admin/tpl/easycredit_overview_list.tpl',
        'easycredit_overview_main.tpl'                        => 'views/admin/tpl/easycredit_overview_main.tpl',
    ],
    'blocks'      => [
        [
            'template' => 'page/checkout/payment.tpl',
            'block'    => 'select_payment',
            'file'     => 'views/blocks/oxpseasycreditselect_payment.tpl',
        ],
        [
            'template' => 'page/details/inc/productmain.tpl',
            'block'    => 'details_productmain_price_value',
            'file'     => 'views/blocks/oxpseasycreditselect_productmain.tpl',
        ],
        [
            'template' => 'page/checkout/basket.tpl',
            'block'    => 'checkout_basket_next_step_bottom',
            'file'     => 'views/blocks/oxpseasycreditselect_basket.tpl',
        ],
        [
            'template' => 'widget/header/minibasket.tpl',
            'block'    => 'dd_layout_page_header_icon_menu_minibasket_list',
            'file'     => 'views/blocks/oxpseasycreditselect_minibasket.tpl',
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block'    => 'shippingAndPayment',
            'file'     => 'views/blocks/oxpseasycredit_order_payment.tpl',
        ],
        [
            'template' => 'page/checkout/inc/basketcontents.tpl',
            'block'    => 'checkout_basketcontents_delcosts',
            'file'     => 'views/blocks/oxpseasycredit_basketcontents_interests.tpl',
        ],
        [
            'template' => 'email/html/order_owner.tpl',
            'block'    => 'email_html_order_owner_grandtotal',
            'file'     => 'views/blocks/email/html/oxpseasycredit_order_owner_grandtotal.tpl',
        ],
        [
            'template' => 'email/html/order_owner.tpl',
            'block'    => 'email_html_order_owner_paymentinfo',
            'file'     => 'views/blocks/email/html/oxpseasycredit_order_owner_paymentinfo.tpl',
        ],
        [
            'template' => 'email/html/order_cust.tpl',
            'block'    => 'email_html_order_cust_grandtotal',
            'file'     => 'views/blocks/email/html/oxpseasycredit_order_cust_grandtotal.tpl',
        ],
        [
            'template' => 'email/html/order_cust.tpl',
            'block'    => 'email_html_order_cust_paymentinfo_top',
            'file'     => 'views/blocks/email/html/oxpseasycredit_order_cust_paymentinfo.tpl',
        ],
        [
            'template' => 'email/plain/order_owner.tpl',
            'block'    => 'email_plain_order_ownergrandtotal',
            'file'     => 'views/blocks/email/plain/oxpseasycredit_order_owner_grandtotal.tpl',
        ],
        [
            'template' => 'email/plain/order_owner.tpl',
            'block'    => 'email_plain_order_ownerpaymentinfo',
            'file'     => 'views/blocks/email/plain/oxpseasycredit_order_owner_paymentinfo.tpl',
        ],
        [
            'template' => 'email/plain/order_cust.tpl',
            'block'    => 'email_plain_order_cust_grandtotal',
            'file'     => 'views/blocks/email/plain/oxpseasycredit_order_cust_grandtotal.tpl',
        ],
        [
            'template' => 'email/plain/order_cust.tpl',
            'block'    => 'email_plain_order_cust_paymentinfo',
            'file'     => 'views/blocks/email/plain/oxpseasycredit_order_cust_paymentinfo.tpl',
        ],
        [
            'template' => 'order_overview.tpl',
            'block'    => 'admin_order_overview_total',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_overview_total.tpl',
        ],
        [
            'template' => 'order_overview.tpl',
            'block'    => 'admin_order_overview_send_form',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_overview_ec_delivery_state.tpl',
        ],
        [
            'template' => 'order_article.tpl',
            'block'    => 'admin_order_article_total',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_article_total.tpl',
        ],
        [
            'template' => 'order_article.tpl',
            'block'    => 'admin_order_article_listitem',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_article_listitem.tpl',
        ],

        [
            'template' => 'order_list.tpl',
            'block'    => 'admin_order_list_colgroup',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_list_colgroups.tpl',
        ],
        [
            'template' => 'order_list.tpl',
            'block'    => 'admin_order_list_filter',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_list_filter.tpl',
        ],
        [
            'template' => 'order_list.tpl',
            'block'    => 'admin_order_list_sorting',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_list_sorting.tpl',
        ],
        [
            'template' => 'order_list.tpl',
            'block'    => 'admin_order_list_item',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_list_item.tpl',
        ],

        [
            'template' => 'order_main.tpl',
            'block'    => 'admin_order_main_form_details',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_main_form_details.tpl',
        ]
    ],
    'settings'    => [
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECBaseUrl',
            'type'  => 'str',
            'value' => 'https://ratenkauf.easycredit.de/ratenkauf-ws/rest',
        ],
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECDealerInterfaceUrl',
            'type'  => 'str',
            'value' => 'https://app.easycredit.de/ratenkauf/transaktionsverwaltung-ws/rest',
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
