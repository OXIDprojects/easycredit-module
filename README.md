# OXID PS EasyCredit Module

# Version 2.0.6

## Description

 * First standalone release of the EasyCredit Module
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

* remove Birthday-Check

### Version 2.0.6

* Fix: Elimination of malfunctions in other payment modules