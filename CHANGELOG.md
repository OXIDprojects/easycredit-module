# Change Log for easyCredit for OXID

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [3.1.0] - UNRELEASED

This release adds support for the easyCredit API v3 and the additional payment
method "easyCredit-Rechnungskauf". API v2 remains supported and stays selectable
per shop, so existing installations keep working after the update.

### Added

- Support for the easyCredit API v3 (`/api/payment/v3/*` and `/api/merchant/v3/*` endpoints), switchable via the new `oxpsECUseV3` module setting
- New payment method `easycreditinvoice` ("easyCredit-Rechnungskauf") next to the existing installment payment, with its own payment template, agreement texts and amount range (50–5000). It is only offered in the checkout while API v3 is active.
- HMAC request signing for API v3 via `Content-signature` header, configurable with `oxpsECUseHMAC` and `oxpsECHMACHeader`
- Connection/credentials check against the v3 integration check endpoint, evaluated when saving the module configuration
- easyCredit web components are loaded in the frontend for the v3 example calculation
- New module settings `oxpsECBaseUrlV3`, `oxpsECDealerInterfaceUrlV3`, `oxpsECUseV3`, `oxpsECUseHMAC`, `oxpsECHMACHeader`
- New database column `oxorder.ECREDISV3ORDER` marking orders created with API v3, so orders placed via v2 and v3 can be processed side by side in the admin backend
- HTTP status code of API calls is now written to the request log
- Extended unit test coverage for the v3 request building and the dispatcher

### Changed

- The example calculation setting `oxpsECExampleUseOwnjQueryUI` was renamed to `oxpsECExampleUseOwnjQuery`. The previously configured value is not carried over — please check this setting in the module configuration after the update.
- API v3 uses the existing webshop ID and token, but transmits them as HTTP Basic authentication. No new credentials have to be requested.
- Additional language keys were added for invoice purchase and for the agreement error messages. Shops that maintain their own copies of the module language files should compare them with the new version.
- Shops using a customized copy of the easyCredit payment template should compare it with the new version: the checkout now uses separate redirect functions for installment and invoice purchase.
- For developers extending the module: several public methods and constants were renamed for the installment/invoice split and to correct the spelling `Instalment` → `Installment`, and the empty class `EasyCreditPayloadFactory` was removed. Own extensions of easyCredit classes should be checked against the new signatures.

### Fixed

- The example calculation returned no price when an article ID was given but the article could not be loaded
- Price calculation of the example calculation on the product detail page
- Frontend JavaScript validation of the payment step
- Payment checkbox error when API v2 is active
- Translations and agreement texts in the Azure theme
- `install.sql` was not executed completely during module activation, and `uninstall.sql` now also deactivates the invoice payment

## [3.0.10] - 2026-04-09

### Fixed

- Add stoken to EasyCreditDispatcher redirect to fix checkSessionChallenge() always failing in frontend
- Add error logging to loadAgreementTxt() instead of silently swallowing exceptions
- Add error message to checkEasyCreditExampleCalulation() when API call fails
- Display collected error messages to user via OXID error display when isEasyCreditPossible() returns false

### Security

- Add CSRF protection (checkSessionChallenge) to EasyCreditDispatcherController::initializeandredirect()
- Migrate serialize/unserialize to json_encode/json_decode for order confirmation response (EasyCreditOrder)
- Add `allowed_classes` restriction to unserialize() in EasyCreditOrderEasyCreditController (backward-compatible with existing serialized data)
- Add `allowed_classes` restriction to unserialize() in EasyCreditSession::getStorage()
- Escape easyCredit API data (paymentPlanTxt) with htmlspecialchars in EasyCreditOrderController
- Replace `getRawValue()` with `value` for payment description in Smarty template
- Replace MD5 with SHA-256 for payment integrity hash in EasyCreditInitializeRequestBuilder
- Add SECURITY.md documenting known security considerations and intentionally unfixed items
