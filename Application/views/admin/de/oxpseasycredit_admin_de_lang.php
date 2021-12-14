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


$sLangName = 'Deutsch';

$aLang = [
    'charset' => 'UTF-8',

    'oxpseasycredit' => 'OXPS Easy Credit',

    'SHOP_MODULE_GROUP_EasyCreditApi'                   => 'API',
    'SHOP_MODULE_GROUP_EasyCreditExampleCalculation'    => 'Modellrechnung',
    'SHOP_MODULE_GROUP_EasyCreditLogging'               => 'Log',
    'SHOP_MODULE_GROUP_EasyCreditCheckout'              => 'Checkout',
    'SHOP_MODULE_oxpsECBaseUrl'                         => 'Basis-URL',
    'SHOP_MODULE_oxpsECDealerInterfaceUrl'              => 'Händler-Interface-URL',
    'SHOP_MODULE_oxpsECWebshopId'                       => 'Webshop-ID',
    'SHOP_MODULE_oxpsECWebshopToken'                    => 'Webshop-Token',
    'SHOP_MODULE_oxpsECExampleCalcArticle'              => 'Modellrechnung auf Artikeldetailseite aktivieren',
    'SHOP_MODULE_oxpsECExampleCalcBasket'               => 'Modellrechnung in der Warenkorbansicht aktivieren',
    'SHOP_MODULE_oxpsECExampleCalcMinibasket'           => 'Modellrechnung im Mini-Warenkorb aktivieren',
    'SHOP_MODULE_oxpsECExampleUseOwnjQueryUI'           => 'Benutze Modul-eigene jQuery UI Bibliothek',
    'HELP_SHOP_MODULE_oxpsECExampleUseOwnjQueryUI'      => 'Performance: Die Javscript-Bibliothek jQuery UI ist normalerweise bereits Bestandteil Ihres OXID-Themes und muss nicht noch einmal vom easyCredit-Modul geladen werden.',
    'SHOP_MODULE_oxpsECLogging'                         => 'Log aktivieren',
    'SHOP_MODULE_oxpsECCheckoutValidConfirm'            => 'Bestellung bestätigen: Validierung der Nachricht von easyCredit',

    'OXPS_EASY_CREDIT_SUMTOTAL_INCLUDES_INTERESTS'            => 'Summe enthält Zinsen',
    'OXPS_EASY_CREDIT_ADMIN_TAB_ONLY_FOR_EASYCREDIT_PAYMENTS' => 'Die Registerkarte ist nur bei Bestellungen mit der Zahlungsart easyCredit verfügbar.',
    'tbclorder_oxpseasycredit'                                => 'ratenkauf by easyCredit',

    'OXPS_EASY_CREDIT_ADMIN_INSTALMENTS_CAPTION'     => 'Informationen zum Ratenkauf:',
    'OXPS_EASY_CREDIT_ADMIN_INTERESTS_VALUE'         => 'Zinsen auf Ratenkauf',
    'OXPS_EASY_CREDIT_ADMIN_TECHNICAL_PROCESS_ID'    => 'Vorgangskennung',
    'OXPS_EASY_CREDIT_ADMIN_TECHNICAL_FUNCTIONAL_ID' => "Fachliche Vorgangskennung",
    'OXPS_EASY_CREDIT_ADMIN_PAYMENT_STATUS'          => "Zahlungsstatus",
    'OXPS_EASY_CREDIT_ADMIN_ECREDCONFIRMRESPONSE'    => "Antwort auf Bestätigungsanfrage",

    'OXPS_EASY_CREDIT_ADMIN_ORDER_DATE'                       => 'Bestelldatum',
    'OXPS_EASY_CREDIT_ADMIN_ORIGINAL_ORDER_VALUE'             => 'Ursprünglicher Bestellwert',
    'OXPS_EASY_CREDIT_ADMIN_ACTUAL_ORDER_VALUE'               => 'Aktueller Bestellwert',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL'                         => 'Rückabwicklung',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_VALUE'                   => 'Widerrufener Betrag',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_DATE'                    => 'Datum des Widerrufes',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_ACCOUNT_DATE'            => 'Datum des Verbuchung des Widerrufes',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON'                  => 'Grund für das Storno',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_FULL'             => 'Vollständiger Widerruf',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_PARTIAL'          => 'Teilweiser Widerruf',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_WARRANTY_FULL'    => 'Rückgabe Garantie / Gewährleistung',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_WARRANTY_PARTIAL' => 'Minderung aus Garantie / Gewährleistung',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_AMOUNT'                  => 'Höhe des Storno',
    'OXPS_EASY_CREDIT_ADMIN_NO_DATA_LOADED'                   => 'Die Daten für diesen Vorgang konnten nicht geladen werden',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_ERROR_COMMON'            => 'Beim Übertragen der Daten an Easy Credit ist ein Problem aufgetreten. Bitte wiederholen Sie den Vorgang zu einem späteren Zeitpunkt erneut',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_ERROR_AMOUNT'            => 'Der zu stornierende Betrag ist inkorrekt. Bitte prüfen Sie ihre Eingabe',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_SUCCESS'                 => 'Die Stornierung wurde erfolgreich an Easy Credit weitergeleitet.',

    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE'                             => 'EasyCredit Händlerstatus',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN'            => 'Lieferung melden',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN_AUSLAUFEND' => 'Lieferung melden (auslaufend)',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_IN_ABRECHNUNG'               => 'In Abrechnung',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ABGERECHNET'                 => 'Abgerechnet',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_AUSLAUFEND'                  => 'Auslaufend',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ERROR'                       => 'Der Händlerstatus konnte nicht abgefragt werden',
    'EASY_CREDIT_ORDER_TYPE'                                            => 'Easy Credit Bestellung',
];
