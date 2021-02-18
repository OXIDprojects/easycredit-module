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
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'oxpseasycredit',
    'title'       => array(
        'de' => 'OXPS Easy Credit',
        'en' => 'OXPS Easy Credit',
    ),
    'description' => array(
        'de' => 'OXPS Easy Credit Modul',
        'en' => 'OXPS Easy Credit Module',
    ),
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '2.0.4',
    'author'      => 'OXID Professional Services',
    'url'         => 'http://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'extend'      => array(
        'payment'       => 'oxps/easycredit/controllers/oxpseasycreditpayment',
        'order'         => 'oxps/easycredit/controllers/oxpseasycreditorder',
        'order_address' => 'oxps/easycredit/controllers/admin/oxpseasycreditorder_address',
        'order_article' => 'oxps/easycredit/controllers/admin/oxpseasycreditorder_article',
        'oxsession'     => 'oxps/easycredit/core/domain/oxpseasycreditoxsession',
        'oxpayment'     => 'oxps/easycredit/core/domain/oxpseasycreditoxpayment',
        'oxbasket'      => 'oxps/easycredit/core/domain/oxpseasycreditoxbasket',
        'oxorder'       => 'oxps/easycredit/core/domain/oxpseasycreditoxorder'
    ),
    'files'       => array(
        'oxpseasycreditmodule' => 'oxps/easycredit/core/oxpseasycreditmodule.php',

        'oxpsEasyCreditLogging'                 => 'oxps/easycredit/core/crosscutting/oxpseasycreditlogging.php',
        'oxpsEasyCreditApiConfig'               => 'oxps/easycredit/core/di/oxpseasycreditapiconfig.php',
        'oxpsEasyCreditConfigException'         => 'oxps/easycredit/core/di/oxpseasycreditconfigexception.php',
        'oxpsEasyCreditCurlException'           => 'oxps/easycredit/core/api/oxpseasycreditcurlexception.php',
        'oxpsEasyCreditDic'                     => 'oxps/easycredit/core/di/oxpseasycreditdic.php',
        'oxpsEasyCreditDicFactory'              => 'oxps/easycredit/core/di/oxpseasycreditdicfactory.php',
        'oxpsEasyCreditDicSession'              => 'oxps/easycredit/core/di/oxpseasycreditdicsession.php',
        'oxpsEasyCreditDicSessionInterface'     => 'oxps/easycredit/core/di/oxpseasycreditdicsessioninterface.php',
        'oxpsEasyCreditDicConfig'               => 'oxps/easycredit/core/di/oxpseasycreditdicconfig.php',
        'oxpsEasyCreditDicConfigInterface'      => 'oxps/easycredit/core/di/oxpseasycreditdicconfiginterface.php',
        'oxpsEasyCreditExampleCalculation'      => 'oxps/easycredit/components/widgets/oxpseasycreditexamplecalculation.php',
        'oxpsEasyCreditExampleCalculationPopup' => 'oxps/easycredit/components/widgets/oxpseasycreditexamplecalculationpopup.php',
        'oxpsEasyCreditHelper'                  => 'oxps/easycredit/core/helper/oxpseasycredithelper.php',
        'oxpsEasyCreditHttpClient'              => 'oxps/easycredit/core/api/oxpseasycredithttpclient.php',
        'oxpsEasyCreditPayloadFactory'          => 'oxps/easycredit/core/payload/oxpseasycreditpayloadfactory.php',
        'oxpsEasyCreditResponseValidator'       => 'oxps/easycredit/core/api/oxpseasycreditresponsevalidator.php',
        'oxpsEasyCreditValidationException'     => 'oxps/easycredit/core/api/oxpseasycreditvalidationexception.php',
        'oxpsEasyCreditWebServiceClient'        => 'oxps/easycredit/core/api/oxpseasycreditwebserviceclient.php',
        'oxpsEasyCreditWebServiceClientFactory' => 'oxps/easycredit/core/api/oxpseasycreditwebserviceclientfactory.php',
        'oxpsEasyCreditDispatcher'              => 'oxps/easycredit/controllers/oxpseasycreditdispatcher.php',
        'oxpsEasyCreditInitializeRequestBuilder' => 'oxps/easycredit/core/helper/oxpseasycreditinitializerequestbuilder.php',
        'oxpsEasyCreditInitializeRequestBuilderInterface' => 'oxps/easycredit/core/helper/oxpseasycreditinitializerequestbuilderinterface.php',
        'oxpsEasyCreditStorage'                 => 'oxps/easycredit/core/dto/oxpseasycreditstorage.php',
        'oxpsEasyCreditException'               => 'oxps/easycredit/core/exception/oxpseasycreditexception.php',
        'oxpsEasyCreditInitializationFailedException' => 'oxps/easycredit/core/exception/oxpseasycreditinitializationfailedexception.php',
        'oxpsEasyCreditAquisitionBorder'        => 'oxps/easycredit/core/domain/oxpseasycreditaquisitionborder.php',

        // Admin
        'oxpsEasyCreditOrderEasyCredit'         => 'oxps/easycredit/controllers/admin/oxpseasycreditordereasycredit.php',
    ),
    'templates'   => array(
        'page/checkout/inc/payment_easycreditinstallment.tpl' => 'oxps/easycredit/views/page/checkout/inc/oxpseasycredit_payment_easycreditinstallment.tpl',
        'oxpseasycredit_examplecalculation.tpl'               => 'oxps/easycredit/views/widgets/oxpseasycredit_examplecalculation.tpl',
        'oxpseasycredit_examplecalculation_popup.tpl'         => 'oxps/easycredit/views/widgets/oxpseasycredit_examplecalculation_popup.tpl',
        'oxpseasycredit_order_easycredit.tpl'                 => 'oxps/easycredit/views/admin/tpl/oxpseasycredit_order_easycredit.tpl',
    ),
    'blocks'      => array(
        array(
            'template' => 'page/checkout/payment.tpl',
            'block' => 'select_payment',
            'file' => 'views/blocks/oxpseasycreditselect_payment.tpl',
        ),
        array(
            'template' => 'page/details/inc/productmain.tpl',
            'block' => 'details_productmain_price_value',
            'file' => 'views/blocks/oxpseasycreditselect_productmain.tpl',
        ),
        array(
            'template' => 'page/checkout/basket.tpl',
            'block' => 'checkout_basket_next_step_bottom',
            'file' => 'views/blocks/oxpseasycreditselect_basket.tpl',
        ),
        array(
            'template' => 'widget/header/minibasket.tpl',
            'block' => 'dd_layout_page_header_icon_menu_minibasket_list',
            'file' => 'views/blocks/oxpseasycreditselect_minibasket.tpl',
        ),
        array(
            'template' => 'page/checkout/order.tpl',
            'block' => 'shippingAndPayment',
            'file' => 'views/blocks/oxpseasycredit_order_payment.tpl',
        ),
        array(
            'template' => 'page/checkout/inc/basketcontents.tpl',
            'block' => 'checkout_basketcontents_delcosts',
            'file' => 'views/blocks/oxpseasycredit_basketcontents_interests.tpl',
        ),
        array(
            'template' => 'email/html/order_owner.tpl',
            'block' => 'email_html_order_owner_grandtotal',
            'file' => 'views/blocks/email/html/oxpseasycredit_order_owner_grandtotal.tpl',
        ),
        array(
            'template' => 'email/html/order_owner.tpl',
            'block' => 'email_html_order_owner_paymentinfo',
            'file' => 'views/blocks/email/html/oxpseasycredit_order_owner_paymentinfo.tpl',
        ),
        array(
            'template' => 'email/html/order_cust.tpl',
            'block' => 'email_html_order_cust_grandtotal',
            'file' => 'views/blocks/email/html/oxpseasycredit_order_cust_grandtotal.tpl',
        ),
        array(
            'template' => 'email/html/order_cust.tpl',
            'block' => 'email_html_order_cust_paymentinfo_top',
            'file' => 'views/blocks/email/html/oxpseasycredit_order_cust_paymentinfo.tpl',
        ),
        array(
            'template' => 'email/plain/order_owner.tpl',
            'block' => 'email_plain_order_ownergrandtotal',
            'file' => 'views/blocks/email/plain/oxpseasycredit_order_owner_grandtotal.tpl',
        ),
        array(
            'template' => 'email/plain/order_owner.tpl',
            'block' => 'email_plain_order_ownerpaymentinfo',
            'file' => 'views/blocks/email/plain/oxpseasycredit_order_owner_paymentinfo.tpl',
        ),
        array(
            'template' => 'email/plain/order_cust.tpl',
            'block' => 'email_plain_order_cust_grandtotal',
            'file' => 'views/blocks/email/plain/oxpseasycredit_order_cust_grandtotal.tpl',
        ),
        array(
            'template' => 'email/plain/order_cust.tpl',
            'block' => 'email_plain_order_cust_paymentinfo',
            'file' => 'views/blocks/email/plain/oxpseasycredit_order_cust_paymentinfo.tpl',
        ),
        array(
            'template' => 'payment_main.tpl',
            'block' => 'admin_payment_main_form',
            'file' => 'views/blocks/admin/oxpseasycredit_payment_main_form.tpl',
        ),
        array(
            'template' => 'order_overview.tpl',
            'block' => 'admin_order_overview_total',
            'file' => 'views/blocks/admin/oxpseasycredit_order_overview_total.tpl',
        ),
        array(
            'template' => 'order_article.tpl',
            'block' => 'admin_order_article_total',
            'file' => 'views/blocks/admin/oxpseasycredit_order_article_total.tpl',
        ),
        array(
            'template' => 'order_article.tpl',
            'block' => 'admin_order_article_listitem',
            'file' => 'views/blocks/admin/oxpseasycredit_order_article_listitem.tpl',
        ),
        array(
            'template' => 'order_main.tpl',
            'block' => 'admin_order_main_form_details',
            'file' => 'views/blocks/admin/oxpseasycredit_order_main_form_details.tpl',
        )
    ),
    'settings'    => array(
        array(
            'group' => 'oxpsEasyCreditApi',
            'name'  => 'oxpsECBaseUrl',
            'type'  => 'str',
            'value' => 'https://ratenkauf.easycredit.de/ratenkauf-ws/rest',
        ),
        array(
            'group' => 'oxpsEasyCreditApi',
            'name'  => 'oxpsECWebshopId',
            'type'  => 'str',
            'value' => '',
        ),
        array(
            'group' => 'oxpsEasyCreditApi',
            'name'  => 'oxpsECWebshopToken',
            'type'  => 'str',
            'value' => '',
        ),
        array(
            'group' => 'oxpsEasyCreditCheckout',
            'name'  => 'oxpsECCheckoutValidConfirm',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group' => 'oxpsEasyCreditExampleCalculation',
            'name'  => 'oxpsECExampleCalcArticle',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group' => 'oxpsEasyCreditExampleCalculation',
            'name'  => 'oxpsECExampleCalcBasket',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group' => 'oxpsEasyCreditExampleCalculation',
            'name'  => 'oxpsECExampleCalcMinibasket',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group' => 'oxpsEasyCreditExampleCalculation',
            'name'  => 'oxpsECExampleUseOwnjQueryUI',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group' => 'oxpsEasyCreditLogging',
            'name'  => 'oxpsECLogging',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'oxpsEasyCreditAquisitionBorder',
            'name'  => 'oxpsECAquisitionBorderValue',
            'type'  => 'str',
            'value' => "",
        ),
        array(
            'group' => 'oxpsEasyCreditAquisitionBorder',
            'name'  => 'oxpsECAquisitionBorderLastUpdate',
            'type'  => 'str',
            'value' => "",
        ),
        array(
            'group' => 'oxpsEasyCreditAquisitionBorder',
            'name'  => 'oxpsECAquBorderUpdateIntervalMin',
            'type'  => 'str',
            'value' => "",
        ),
        array(
            'group' => 'oxpsEasyCreditAquisitionBorder',
            'name'  => 'oxpsECAquBorderConsiderFrontend',
            'type'  => 'bool',
            'value' => false,
        )
    ),
    'events'      => array(
        'onActivate'   => 'oxpsEasyCreditModule::onActivate',
        'onDeactivate' => 'oxpsEasyCreditModule::onDeactivate',
    ),
);
