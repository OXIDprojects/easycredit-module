# easyCredit for OXID

easyCredit payment integration ("easyCredit-Ratenkauf" and "easyCredit-Rechnungskauf") for OXID eShop 7.1 and above.

## Documentation

There is no separate official documentation for this module. This README covers installation, configuration and the
basic handling of the module. Changes between versions are listed in the [CHANGELOG](CHANGELOG.md), known security
considerations in [SECURITY.md](SECURITY.md).

Contract, credentials and API questions: [easyCredit partner portal](https://partner.easycredit-ratenkauf.de).

## Features

* Payment method **easyCredit-Ratenkauf** (installment purchase, `easycreditinstallment`, default range 200 – 10,000 EUR)
* Payment method **easyCredit-Rechnungskauf** (invoice purchase, `easycreditinvoice`, default range 50 – 5,000 EUR,
  only available with API v3)
* Support for easyCredit API v3 and the legacy API v2, switchable per shop
* Optional HMAC signing of API v3 requests
* Example calculation (installment plan) on the product details page, in the basket and in the mini basket
* Order handling in the OXID admin: additional tab "easyCredit Informationen" in the order view with transaction overview,
  delivery reporting and cancellation (storno)
* easyCredit orders are protected against changes in the admin (discounts, added articles etc.)
* Optional request logging

## Branch Compatibility

* b-7.0.x module branch (module version 4.x) is compatible with OXID eShop compilation 7.1 and above
* b-6.1.x module branch (module version 3.x) is compatible with OXID eShop compilation 6.3 to 6.5

## Requirements

* OXID eShop >= 7.1 (Twig themes only, Smarty is not supported)
* PHP >= 8.1 with `ext-curl`
* An easyCredit merchant contract with webshop ID and webshop token

## Installation

Add the module to your project with Composer:

```bash
composer require oxid-professional-services/easycredit-module:^4.0
```

Activate the module in the administration area (Extensions → Modules → easyCredit-Ratenkauf for OXID) or via console:

```bash
vendor/bin/oe-console oe:module:activate osceasycredit
```

On activation the module

* runs its database migrations (e.g. adds the easyCredit columns to `oxorder`),
* creates the payment methods `easycreditinstallment` and `easycreditinvoice` and assigns them to the
  standard shipping method `oxidstandard`.

Afterwards clear the shop cache (`tmp`) and regenerate the database views.

## Configuration

All settings can be found in the module configuration (Extensions → Modules → easyCredit-Ratenkauf for OXID → Settings).

### API

| Setting                                         | Description                                                                                  |
|-------------------------------------------------|----------------------------------------------------------------------------------------------|
| Webshop ID / Webshop token                      | Credentials from easyCredit. The same credentials are used for API v2 and API v3.            |
| Use Easycredit API Version 3                    | Enabled by default. Required for easyCredit-Rechnungskauf. Disable only to stay on API v2.   |
| Activate Easycredit HMAC validation / HMAC secret | Signs API v3 requests. Only enable if HMAC has been activated for your webshop at easyCredit. |
| Base URL / Dealer-Interface-URL (v2 and v3)     | Endpoints of the easyCredit APIs. The default values usually don't need to be changed.       |

When the settings are saved with API v3 enabled, the module performs an integration check against easyCredit and
reports whether the credentials are valid.

### Checkout

* **Confirm order: Validation of the message from easyCredit** – validates the response of easyCredit when the
  order is finalized (recommended, enabled by default).

### Example calculation

* Enable or disable the example calculation on the product details page, in the basket and in the mini basket.
* **Use modules own jQuery and jQuery UI library** – disable this if your theme already provides jQuery UI.

### Log

* **Activate log** – writes the API requests and responses to `source/log/easycredit-module.log`. Enable only for
  debugging, the log contains order and customer data.

### Payment methods

The payment methods can be adjusted like any other payment method in Shop Settings → Payment Methods (e.g. amount
range, countries, user groups). Please keep the amount ranges within the limits of your easyCredit contract.
easyCredit-Rechnungskauf is only offered in the checkout while API v3 is enabled.

## Order handling

easyCredit orders are processed in the admin under Administer Orders → Orders, tab "easyCredit Informationen". There you can see
the transaction status at easyCredit, report the delivery and cancel the order.

Orders placed with API v2 and API v3 can be processed side by side; the module remembers which API was used for
each order.

## Update

* Update the module via Composer, e.g. `composer update oxid-professional-services/easycredit-module`
* Run the module migrations (they also run automatically on the next module activation):
  ```bash
  vendor/bin/oe-eshop-doctrine_migration migrations:migrate osceasycredit
  ```
* Clear the shop cache and regenerate the database views
* Check the [CHANGELOG](CHANGELOG.md) for new settings and changed templates or language keys, especially if you use
  customized copies of the module templates

## Uninstall

* Deactivate the module in the administration area (this deactivates the easyCredit payment methods)
* Remove the module via Composer:
  ```bash
  composer remove oxid-professional-services/easycredit-module
  ```

## Limitations

* The payment is finalized via browser redirect only, there is no server-to-server webhook. If the customer does not
  return to the shop after approval at easyCredit, the order has to be checked manually. See [SECURITY.md](SECURITY.md).
* easyCredit-Rechnungskauf is only available with API v3.

## Merging Strategy

* The b-6.1.x branch (OXID 6) is not merged automatically into the b-7.0.x branch (OXID 7)
* If something changes in the b-6.1.x branch, it must be ported to the b-7.0.x branch and vice versa

## Development

Static code analysis (phpcs, phpstan, phpmd):

```bash
composer static
```

The unit tests are located in `tests/Unit`, the PHPUnit configuration in `tests/phpunit.xml`. Running them requires
a shop installation with dev dependencies.
