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

    'SHOP_MODULE_GROUP_oxpsEasyCreditApi'                     => 'API',
    'SHOP_MODULE_GROUP_oxpsEasyCreditExampleCalculation'      => 'Modellrechnung',
    'SHOP_MODULE_GROUP_oxpsEasyCreditLogging'                 => 'Log',
    'SHOP_MODULE_GROUP_oxpsEasyCreditCheckout'                => 'Checkout',
    'SHOP_MODULE_GROUP_oxpsEasyCreditAquisitionBorder'        => 'Ankaufobergrenze',
    'SHOP_MODULE_oxpsECBaseUrl'                               => 'Basis-URL',
    'SHOP_MODULE_oxpsECWebshopId'                             => 'Webshop-ID',
    'SHOP_MODULE_oxpsECWebshopToken'                          => 'Webshop-Token',
    'SHOP_MODULE_oxpsECExampleCalcArticle'                    => 'Modellrechnung auf Artikeldetailseite aktivieren',
    'SHOP_MODULE_oxpsECExampleCalcBasket'                     => 'Modellrechnung in der Warenkorbansicht aktivieren',
    'SHOP_MODULE_oxpsECExampleCalcMinibasket'                 => 'Modellrechnung im Mini-Warenkorb aktivieren',
    'SHOP_MODULE_oxpsECExampleUseOwnjQueryUI'                 => 'Benutze Modul-eigene jQuery UI Bibliothek',
    'HELP_SHOP_MODULE_oxpsECExampleUseOwnjQueryUI'            => 'Performance: Die Javscript-Bibliothek jQuery UI ist normalerweise bereits Bestandteil Ihres OXID-Themes und muss nicht noch einmal vom easyCredit-Modul geladen werden.',
    'SHOP_MODULE_oxpsECLogging'                               => 'Log aktivieren',
    'SHOP_MODULE_oxpsECCheckoutValidConfirm'                  => 'Bestellung bestätigen: Validierung der Nachricht von easyCredit',
    'SHOP_MODULE_oxpsECAquisitionBorderValue'                 => 'Aktuelle Ankaufobergrenze [EUR]',
    'HELP_SHOP_MODULE_oxpsECAquisitionBorderValue'            => 'Die vertraglich vereinbarte Ankaufobergrenze ist Summe, bis zu deren Höhe die TeamBank Ihnen einen Ratenkauf anbieten und Forderungen gegenüber Kunden übernehmen kann.',
    'SHOP_MODULE_oxpsECAquisitionBorderLastUpdate'            => 'Letzte Abfrage von easyCredit',
    'HELP_SHOP_MODULE_oxpsECAquisitionBorderLastUpdate'       => 'Wann wurde die Ankaufobergrenze zuletzt von easyCredit aktualisiert?',
    'SHOP_MODULE_oxpsECAquBorderUpdateIntervalMin'            => 'Abfrageintervall in Minuten',
    'HELP_SHOP_MODULE_oxpsECAquBorderUpdateIntervalMin'       => 'Angabe des Intervall in Minuten, in denen die Ankaufobergrenze von easyCredit abgefragt werden soll. Lassen Sie das Feld leer, wenn Sie die Ankaufobergrenze nicht abfragen möchten. 1440 = 1x täglich',
    'SHOP_MODULE_oxpsECAquBorderConsiderFrontend'             => 'Berücksichtigung der Ankaufobergrenze im Webshop Frontend',
    'HELP_SHOP_MODULE_oxpsECAquBorderConsiderFrontend'        => 'Wenn gewählt, wird ratenkauf by easyCredit nur als Zahlungsart angezeigt, wenn der Warenwert unterhalb der Ankaufobergrenze liegt.',

    'OXPS_EASY_CREDIT_SUMTOTAL_INCLUDES_INTERESTS'            => 'Summe enthält Zinsen',
    'OXPS_EASY_CREDIT_ADMIN_TAB_ONLY_FOR_EASYCREDIT_PAYMENTS' => 'Die Registerkarte ist nur bei Bestellungen mit der Zahlungsart easyCredit verfügbar.',
    'tbclorder_oxpseasycredit'                                => 'ratenkauf by easyCredit',

    'OXPS_EASY_CREDIT_ADMIN_INSTALMENTS_CAPTION'              => 'Informationen zum Ratenkauf:',
    'OXPS_EASY_CREDIT_ADMIN_INTERESTS_VALUE'                  => 'Zinsen auf Ratenkauf',
    'OXPS_EASY_CREDIT_ADMIN_TECHNICAL_PROCESS_ID'             => 'Vorgangskennung',
    'OXPS_EASY_CREDIT_ADMIN_TECHNICAL_FUNCTIONAL_ID'          => "Fachliche Vorgangskennung",
    'OXPS_EASY_CREDIT_ADMIN_PAYMENT_STATUS'                   => "Zahlungsstatus",
    'OXPS_EASY_CREDIT_ADMIN_ECREDCONFIRMRESPONSE'             => "Antwort auf Bestätigungsanfrage",
    'OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE'           => 'Ankaufobergrenze',
    'HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE'      => 'Die vertraglich vereinbarte Ankaufobergrenze ist Summe, bis zu deren Höhe die TeamBank Ihnen einen Ratenkauf anbieten und Forderungen gegenüber Kunden übernehmen kann.',
    'OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE'      => 'Letzte Aktualisierung',
    'HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE' => 'Die Ankaufobergrenze wird periodisch nach festgelegtem Intervall (siehe Module-Settings) sowie zusätzlich nach jedem erfolgreichen Ratenkauf abgefragt.',
];
