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
    'version'     => '4.0.3',
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
        OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditModuleConfigurationController::class,
        OxidEsales\Eshop\Application\Controller\Admin\OrderAddress::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderAddressController::class,
        OxidEsales\Eshop\Application\Controller\Admin\OrderArticle::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderArticleController::class,
        OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderOverviewController::class,
        OxidEsales\Eshop\Application\Controller\Admin\OrderMain::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderMainController::class,
        OxidEsales\Eshop\Application\Controller\Admin\OrderList::class => OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderListController::class,

        # Extending core classes
        \OxidEsales\Eshop\Core\Session::class => OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditSession::class,
        \OxidEsales\Eshop\Application\Model\Payment::class => OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditPayment::class,
        \OxidEsales\Eshop\Application\Model\Basket::class => OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditBasket::class,
        \OxidEsales\Eshop\Application\Model\Order::class => OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditOrder::class,
        \OxidEsales\Eshop\Core\ViewConfig::class => OxidSolutionCatalysts\EasyCredit\Core\EasyCreditViewConfig::class,
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
            'name'  => 'oxpsECBaseUrlV3',
            'type'  => 'str',
            'value' => 'https://ratenkauf.easycredit.de',
        ],
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECDealerInterfaceUrlV3',
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
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECUseV3',
            'type'  => 'bool',
            'value' => true,
        ],
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECUseHMAC',
            'type'  => 'bool',
            'value' => false,
        ],
        [
            'group' => 'EasyCreditApi',
            'name'  => 'oxpsECHMACHeader',
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
            'name'  => 'oxpsECExampleUseOwnjQuery',
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
