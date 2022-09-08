# OXID Solution Catalyst easyCredit-Ratenkauf Module

# Version 3.0.8

## Description

 * First standalone release of the easyCredit-Ratenkauf Module
 * Supports payment type installment plan
 * Supports handling over the oxid admin backend only

## Installation

Use Composer to add the module to your project
```bash
composer require oxid-professional-services/easycredit-module
```

 * Activate the module in administration area
 * clear tmp and regenerate views
 * Make sure to take care of all the settings, options and credentials described in the user manual

## Uninstall

 * Deactivate the module in administration area
 * remove "oxid-professional-services/easycredit-module" from your composer.json

Run Composer again to remove Module from vendor
```bash
composer update
```

## Changelog

### Version 1.0.0

* Version for OXID4 installable via FTP

### Version 2.0.0

* Version for OXID6 installable via Composer

### Version 2.0.4

* Function-Check for OXID 6.2.3
* easyCredit Orders are not changable (Discounts, add Articles...) in OXID-Backend

### Version 2.0.5

* Birthday is not required
* Possiblility to use own jqueryUI-Lib in Frontend

### Version 2.0.6

* Fix: Elimination of malfunctions in other payment modules

### Version 3.0.0

* Introduce Namespaces
* No more support for OXID <= v6.0
* Integrate new API für dealer gateway
* Transaction-Overview in Backend
* Storno in Backend

### Version 3.0.1

* Bugfixes

### Version 3.0.2

* Bugfixes

### Version 3.0.3

* Bugfixes

### Version 3.0.4

* transfer OrderNr to EasyCredit
* remove Ankaufsobergrenze

### Version 3.0.5

* remove Payment-Costs in Checkout
* add better default-values for payment

### Version 3.0.6

* improve Backwards-compatiblity to PHP7.2
* calculate Rateplan only in Payment-Pricerange (by default 200 < x < 10000)

### Version 3.0.7

* bugfix-release

### Version 3.0.8

* Rebranding easyCredit-Ratenkauf