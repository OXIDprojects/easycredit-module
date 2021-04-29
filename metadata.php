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
 * @package       easycredit
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => 'oxpseasycredit',
    'title'       => [
        'de' => 'OXPS Easy Credit',
        'en' => 'OXPS Easy Credit',
    ],
    'description' => [
        'de' => 'OXPS Easy Credit Modul',
        'en' => 'OXPS Easy Credit Module',
    ],
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '2.0.4',
    'author'      => 'OXID Professional Services',
    'url'         => 'http://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'controllers' => [
        //'oxpsEasyCreditDispatcher' => 'oxps/easycredit/controllers/oxpseasycreditdispatcher.php',
        'oxpsEasyCreditDispatcher'          => \OxidProfessionalServices\EasyCredit\Application\Controller\EasyCreditDispatcherController::class,
        # Admin
        //'oxpsEasyCreditOrderEasyCredit' => 'oxps/easycredit/controllers/admin/oxpseasycreditordereasycredit.php',
        'oxpsEasyCreditOrderEasyCredit'     => \OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderEasyCreditController::class,
        # Widgets
        'easycreditexamplecalculation'      => \OxidProfessionalServices\EasyCredit\Application\Component\Widget\EasyCreditExampleCalculation::class,
        'easycreditexamplecalculationpopup' => \OxidProfessionalServices\EasyCredit\Application\Component\Widget\EasyCreditExampleCalculationPopup::class,

    ],
    'extend'      => [
        # extended controller
        //'payment'       => 'oxps/easycredit/controllers/oxpseasycreditpayment',
        \OxidEsales\Eshop\Application\Controller\PaymentController::class  => \OxidProfessionalServices\EasyCredit\Application\Controller\EasyCreditPaymentController::class,
        //'order'         => 'oxps/easycredit/controllers/oxpseasycreditorder',
        \OxidEsales\Eshop\Application\Controller\OrderController::class    => \OxidProfessionalServices\EasyCredit\Application\Controller\EasyCreditOrderController::class,

        # Extended admin controller
        //'order_address' => 'oxps/easycredit/controllers/admin/oxpseasycreditorder_address',
        \OxidEsales\Eshop\Application\Controller\Admin\OrderAddress::class => \OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderAddressController::class,
        //'order_article' => 'oxps/easycredit/controllers/admin/oxpseasycreditorder_article',
        \OxidEsales\Eshop\Application\Controller\Admin\OrderArticle::class => \OxidProfessionalServices\EasyCredit\Application\Controller\Admin\EasyCreditOrderArticleController::class,

        # Extending core classes
        //'oxsession'     => 'oxps/easycredit/core/domain/oxpseasycreditoxsession',
        \OxidEsales\Eshop\Core\Session::class                              => \OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditSession::class,
        //'oxpayment'     => 'oxps/easycredit/core/domain/oxpseasycreditoxpayment',
        \OxidEsales\Eshop\Application\Model\Payment::class                 => \OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditPayment::class,
        //'oxbasket'      => 'oxps/easycredit/core/domain/oxpseasycreditoxbasket',
        \OxidEsales\Eshop\Application\Model\Basket::class                  => \OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditBasket::class,
        //'oxorder'       => 'oxps/easycredit/core/domain/oxpseasycreditoxorder'
        \OxidEsales\Eshop\Application\Model\Order::class                   => \OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditOrder::class
    ],
    'templates'   => [
        'page/checkout/inc/payment_easycreditinstallment.tpl' => 'oxps/easycredit/Application/views/page/checkout/inc/oxpseasycredit_payment_easycreditinstallment.tpl',
        'oxpseasycredit_examplecalculation.tpl'               => 'oxps/easycredit/Application/views/widgets/oxpseasycredit_examplecalculation.tpl',
        'oxpseasycredit_examplecalculation_popup.tpl'         => 'oxps/easycredit/Application/views/widgets/oxpseasycredit_examplecalculation_popup.tpl',
        'oxpseasycredit_order_easycredit.tpl'                 => 'oxps/easycredit/Application/views/admin/tpl/oxpseasycredit_order_easycredit.tpl',
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
            'template' => 'payment_main.tpl',
            'block'    => 'admin_payment_main_form',
            'file'     => 'views/blocks/admin/oxpseasycredit_payment_main_form.tpl',
        ],
        [
            'template' => 'order_overview.tpl',
            'block'    => 'admin_order_overview_total',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_overview_total.tpl',
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
            'template' => 'order_main.tpl',
            'block'    => 'admin_order_main_form_details',
            'file'     => 'views/blocks/admin/oxpseasycredit_order_main_form_details.tpl',
        ],
    ],
    'settings'    => [
        [
            'group' => 'oxpsEasyCreditApi',
            'name'  => 'oxpsECBaseUrl',
            'type'  => 'str',
            'value' => 'https://ratenkauf.easycredit.de/ratenkauf-ws/rest',
        ],
        [
            'group' => 'oxpsEasyCreditApi',
            'name'  => 'oxpsECWebshopId',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'oxpsEasyCreditApi',
            'name'  => 'oxpsECWebshopToken',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'oxpsEasyCreditCheckout',
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
            'group' => 'oxpsEasyCreditLogging',
            'name'  => 'oxpsECLogging',
            'type'  => 'bool',
            'value' => false,
        ],
        [
            'group' => 'EasyCreditAquisitionBorder',
            'name'  => 'oxpsECAquisitionBorderValue',
            'type'  => 'str',
            'value' => "",
        ],
        [
            'group' => 'EasyCreditAquisitionBorder',
            'name'  => 'oxpsECAquisitionBorderLastUpdate',
            'type'  => 'str',
            'value' => "",
        ],
        [
            'group' => 'EasyCreditAquisitionBorder',
            'name'  => 'oxpsECAquBorderUpdateIntervalMin',
            'type'  => 'str',
            'value' => "",
        ],
        [
            'group' => 'EasyCreditAquisitionBorder',
            'name'  => 'oxpsECAquBorderConsiderFrontend',
            'type'  => 'bool',
            'value' => false,
        ],
    ],
    'events'      => [
        'onActivate'   => '\OxidProfessionalServices\EasyCredit\Core\Events::onActivate',
        'onDeactivate' => '\OxidProfessionalServices\EasyCredit\Core\Events::onDeactivate',
    ],
];
