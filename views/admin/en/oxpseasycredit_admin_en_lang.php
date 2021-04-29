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

$aLang = array(
    'charset' => 'UTF-8',

    'oxpseasycredit' => 'OXPS Easy Credit',

    'SHOP_MODULE_GROUP_oxpsEasyCreditApi'                     => 'API',
    'SHOP_MODULE_GROUP_oxpsEasyCreditExampleCalculation'      => 'Example Calculation',
    'SHOP_MODULE_GROUP_oxpsEasyCreditLogging'                 => 'Log',
    'SHOP_MODULE_GROUP_oxpsEasyCreditCheckout'                => 'Checkout',
    'SHOP_MODULE_GROUP_oxpsEasyCreditAquisitionBorder'        => 'TR: Ankaufobergrenze',
    'SHOP_MODULE_oxpsECBaseUrl'                               => 'Base URL',
    'SHOP_MODULE_oxpsECWebshopId'                             => 'Web shop ID',
    'SHOP_MODULE_oxpsECWebshopToken'                          => 'Web shop token',
    'SHOP_MODULE_oxpsECExampleCalcArticle'                    => 'Activate example calculation on product details page',
    'SHOP_MODULE_oxpsECExampleCalcBasket'                     => 'Activate example calculation on basket page',
    'SHOP_MODULE_oxpsECExampleCalcMinibasket'                 => 'Activate example calculation on mini basket widget',
    'SHOP_MODULE_oxpsECExampleUseOwnjQueryUI'                 => 'Activate example calculation on mini basket widget',
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

    'OXPS_EASY_CREDIT_SUMTOTAL_INCLUDES_INTERESTS'            => 'TR: Summe enthält Zinsen',
    'OXPS_EASY_CREDIT_ADMIN_TAB_ONLY_FOR_EASYCREDIT_PAYMENTS' => 'TR: Die Registerkarte ist nur bei Bestellungen mit der Zahlungsart easyCredit verfügbar.',
    'tbclorder_oxpseasycredit'                                => 'TR: ratenkauf by easyCredit',

    'OXPS_EASY_CREDIT_ADMIN_INSTALMENTS_CAPTION'              => 'TR: Informationen zum Ratenkauf:',
    'OXPS_EASY_CREDIT_ADMIN_INTERESTS_VALUE'                  => 'TR: Zinsen auf Ratenkauf',
    'OXPS_EASY_CREDIT_ADMIN_TECHNICAL_PROCESS_ID'             => 'TR: Vorgangskennung',
    'OXPS_EASY_CREDIT_ADMIN_TECHNICAL_FUNCTIONAL_ID'          => "TR: Fachliche Vorgangskennung",
    'OXPS_EASY_CREDIT_ADMIN_PAYMENT_STATUS'                   => "TR: Zahlungsstatus",
    'OXPS_EASY_CREDIT_ADMIN_ECREDCONFIRMRESPONSE'             => "TR: Antwort auf Bestätigungsanfrage",
    'OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE'           => 'TR: Ankaufobergrenze',
    'HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE'      => 'TR: Die vertraglich vereinbarte Ankaufobergrenze ist Summe, bis zu deren Höhe die TeamBank Ihnen einen Ratenkauf anbieten und Forderungen gegenüber Kunden übernehmen kann.',
    'OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE'      => 'TR: Letzte Aktualisierung',
    'HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE' => 'TR: Die Ankaufobergrenze wird periodisch nach festgelegtem Intervall (siehe Module-Settings) sowie zusätzlich nach jedem erfolgreichen Ratenkauf abgefragt.',
);
