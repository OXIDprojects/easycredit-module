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


$sLangName = 'English';

$aLang = [
    'charset' => 'UTF-8',

    'oxpseasycredit' => 'OXPS Easy Credit',

    'SHOP_MODULE_GROUP_EasyCreditApi'                         => 'API',
    'SHOP_MODULE_GROUP_EasyCreditExampleCalculation'          => 'Example Calculation',
    'SHOP_MODULE_GROUP_EasyCreditLogging'                     => 'Log',
    'SHOP_MODULE_GROUP_EasyCreditCheckout'                    => 'Checkout',
    'SHOP_MODULE_GROUP_EasyCreditAquisitionBorder'            => 'TR: Ankaufobergrenze',
    'SHOP_MODULE_oxpsECBaseUrl'                               => 'Base URL',
    'SHOP_MODULE_oxpsECDealerInterfaceUrl'                    => 'Dealer-Interface-URL',
    'SHOP_MODULE_oxpsECWebshopId'                             => 'Web shop ID',
    'SHOP_MODULE_oxpsECWebshopToken'                          => 'Web shop token',
    'SHOP_MODULE_oxpsECExampleCalcArticle'                    => 'Activate example calculation on product details page',
    'SHOP_MODULE_oxpsECExampleCalcBasket'                     => 'Activate example calculation on basket page',
    'SHOP_MODULE_oxpsECExampleCalcMinibasket'                 => 'Activate example calculation on mini basket widget',
    'SHOP_MODULE_oxpsECExampleUseOwnjQueryUI'                 => 'TR: Benutze Modul-eigene jQuery UI Bibliothek',
    'HELP_SHOP_MODULE_oxpsECExampleUseOwnjQueryUI'            => 'TR: Performance: Die Javscript-Bibliothek jQuery UI ist normalerweise bereits Bestandteil Ihres OXID-Themes und muss nicht noch einmal vom easyCredit-Modul geladen werden.',
    'SHOP_MODULE_oxpsECLogging'                               => 'Activate log',
    'SHOP_MODULE_oxpsECCheckoutValidConfirm'                  => 'TR: Bestellung bestätigen: Validierung der Nachricht von easyCredit',
    'SHOP_MODULE_oxpsECAquisitionBorderValue'                 => 'TR: Aktuelle Ankaufobergrenze [EUR]',
    'HELP_SHOP_MODULE_oxpsECAquisitionBorderValue'            => 'TR: Die vertraglich vereinbarte Ankaufobergrenze ist Summe, bis zu deren Höhe die TeamBank Ihnen einen Ratenkauf anbieten und Forderungen gegenüber Kunden übernehmen kann.',
    'SHOP_MODULE_oxpsECAquisitionBorderLastUpdate'            => 'TR: Letzte Abfrage von easyCredit',
    'HELP_SHOP_MODULE_oxpsECAquisitionBorderLastUpdate'       => 'TR: Wann wurde die Ankaufobergrenze zuletzt von easyCredit aktualisiert?',
    'SHOP_MODULE_oxpsECAquBorderUpdateIntervalMin'            => 'TR: Abfrageintervall in Minuten',
    'HELP_SHOP_MODULE_oxpsECAquBorderUpdateIntervalMin'       => 'TR: Angabe des Intervall in Minuten, in denen die Ankaufobergrenze von easyCredit abgefragt werden soll. Lassen Sie das Feld leer, wenn Sie die Ankaufobergrenze nicht abfragen möchten. 1440 = 1x täglich',
    'SHOP_MODULE_oxpsECAquBorderConsiderFrontend'             => 'TR: Berücksichtigung der Ankaufobergrenze im Webshop Frontend',
    'HELP_SHOP_MODULE_oxpsECAquBorderConsiderFrontend'        => 'TR: Wenn gewählt, wird ratenkauf by easyCredit nur als Zahlungsart angezeigt, wenn der Warenwert unterhalb der Ankaufobergrenze liegt.',

    'OXPS_EASY_CREDIT_ADMIN_INSTALMENTS_CAPTION'     => 'TR: Informationen zum Ratenkauf:',
    'OXPS_EASY_CREDIT_ADMIN_INTERESTS_VALUE'         => 'TR: Zinsen auf Ratenkauf',
    'OXPS_EASY_CREDIT_ADMIN_TECHNICAL_PROCESS_ID'    => 'TR: Vorgangskennung',
    'OXPS_EASY_CREDIT_ADMIN_TECHNICAL_FUNCTIONAL_ID' => "TR: Fachliche Vorgangskennung",
    'OXPS_EASY_CREDIT_ADMIN_PAYMENT_STATUS'          => "TR: Zahlungsstatus",
    'OXPS_EASY_CREDIT_ADMIN_ECREDCONFIRMRESPONSE'    => "TR: Antwort auf Bestätigungsanfrage",
    'OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE'  => 'TR: Ankaufobergrenze',

    'OXPS_EASY_CREDIT_ADMIN_ORDER_DATE'                       => 'TR: Bestelldatum',
    'OXPS_EASY_CREDIT_ADMIN_ORIGINAL_ORDER_VALUE'             => 'TR: Ursprünglicher Bestellwert',
    'OXPS_EASY_CREDIT_ADMIN_ACTUAL_ORDER_VALUE'               => 'TR: Aktueller Bestellwert',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL'                         => 'TR: Rückabwicklung',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_VALUE'                   => 'TR: Widerrufener Betrag',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_DATE'                    => 'TR: Datum des Widerrufes',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_ACCOUNT_DATE'            => 'TR: Datum des Verbuchung des Widerrufes',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON'                  => 'TR: Grund für das Storno',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_FULL'             => 'TR: Vollständiger Widerruf',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_PARTIAL'          => 'TR: Teilweiser Widerruf',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_WARRANTY_FULL'    => 'TR: Rückgabe Garantie / Gewährleistung',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_WARRANTY_PARTIAL' => 'TR: Minderung aus Garantie / Gewährleistung',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_AMOUNT'                  => 'TR: Höhe des Storno',
    'OXPS_EASY_CREDIT_ADMIN_NO_DATA_LOADED'                   => 'TR: Die Daten für diesen Vorgang konnten nicht geladen werden',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_ERROR_COMMON'            => 'TR: Beim Übertragen der Daten an Easy Credit ist ein Problem aufgetreten. Bitte wiederholen Sie den Vorgang zu einem späteren Zeitpunkt erneut',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_ERROR_AMOUNT'            => 'TR: Der zu stornierende Betrag ist inkorrekt. Bitte prüfen Sie ihre Eingabe',
    'OXPS_EASY_CREDIT_ADMIN_REVERSAL_SUCCESS'                 => 'TR: Die Stornierung wurde erfolgreich an Easy Credit weitergeleitet.',

    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE'                             => 'TR: EasyCredit Händlerstatus',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN'            => 'TR: Lieferung melden',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_LIEFERUNG_MELDEN_AUSLAUFEND' => 'TR: Lieferung melden (auslaufend)',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_IN_ABRECHNUNG'               => 'TR: In Abrechnung',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ABGERECHNET'                 => 'TR: Abgerechnet',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_AUSLAUFEND'                  => 'TR: Auslaufend',
    'OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE_ERROR'                       => 'TR: Der Händlerstatus konnte nicht abgefragt werden',
    'EASY_CREDIT_ORDER_TYPE'                                            => 'TR: Easy Credit Bestellung',

    'HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE'      => 'TR: Die vertraglich vereinbarte Ankaufobergrenze ist Summe, bis zu deren Höhe die TeamBank Ihnen einen Ratenkauf anbieten und Forderungen gegenüber Kunden übernehmen kann.',
    'OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE'      => 'TR: Letzte Aktualisierung',
    'HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE' => 'TR: Die Ankaufobergrenze wird periodisch nach festgelegtem Intervall (siehe Module-Settings) sowie zusätzlich nach jedem erfolgreichen Ratenkauf abgefragt.',
];