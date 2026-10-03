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
- Orders are marked as paid (`oxorder.oxpaid`) as soon as easyCredit has accepted the delivery report that is sent when the order is shipped in the admin backend. easyCredit transfers the money later, but the payment is guaranteed at this point, so shop operators and connected ERP systems see the order as paid right away. Orders that easyCredit already reports as in billing or billed are marked as paid as well, an existing paid date is not changed.
- The delivery is also reported to easyCredit when the order is shipped from the "Main" tab of the order, not only from the "Overview" tab
- [0007988](https://bugs.oxid-esales.com/view.php?id=7988): Confirmation mails for refunds and cancellations triggered in the backend. Two new module settings in the module configuration (group "Confirmation mails") decide who is notified, separately per event: `oxpsECRefundMailRecipient` and `oxpsECCancelMailRecipient`, each with `0` no mail (default), `1` customer, `2` shop owner, `3` both. Defaults are `0`, so updating the module does not start sending mail to existing customers unannounced. The refund mail is sent once easyCredit has accepted a reversal triggered in the "easyCredit Informationen" tab of the order. The cancellation mail is sent when an easyCredit order is cancelled in the order list and states the refunded amount if the cancellation reversed the order. Like the other frontend texts of the module, the mails are available in German only. A problem with sending a mail never aborts the backend action.
- New module setting "Refund automatically when an order is cancelled" (`oxpsECAutomatedRefundOnCancel`, group "Cancellation and refund", off by default). With the option on, cancelling an easyCredit order in the backend fully reverses the order value that is still open at easyCredit. If easyCredit rejects the reversal, the cancellation stays in place and the merchant is asked to reverse the order by hand in the "easyCredit Informationen" tab.
- HTTP status code of API calls is now written to the request log
- Extended unit test coverage for the v3 request building and the dispatcher

### Changed

- The example calculation setting `oxpsECExampleUseOwnjQueryUI` was renamed to `oxpsECExampleUseOwnjQuery`. The previously configured value is not carried over — please check this setting in the module configuration after the update.
- API v3 uses the existing webshop ID and token, but transmits them as HTTP Basic authentication. No new credentials have to be requested.
- Additional language keys were added for invoice purchase and for the agreement error messages. Shops that maintain their own copies of the module language files should compare them with the new version.
- Shops using a customized copy of the easyCredit payment template should compare it with the new version: the checkout now uses separate redirect functions for installment and invoice purchase.
- For developers extending the module: several public methods and constants were renamed for the installment/invoice split and to correct the spelling `Instalment` → `Installment`, and the empty class `EasyCreditPayloadFactory` was removed. Own extensions of easyCredit classes should be checked against the new signatures.

### Fixed

- A reversal in the "easyCredit Informationen" tab is only reported as successful once easyCredit has accepted it. Before, the success message was shown even if easyCredit rejected the reversal or could not be reached.
- The example calculation returned no price when an article ID was given but the article could not be loaded
- Price calculation of the example calculation on the product detail page
- Frontend JavaScript validation of the payment step
- Payment checkbox error when API v2 is active
- Translations and agreement texts in the Azure theme
- `install.sql` was not executed completely during module activation, and `uninstall.sql` now also deactivates the invoice payment
- Example calculation (API v3): the widget no longer ends in a `TypeError` ("array_key_last(): Argument #1 ($array) must be of type array, null given") when easyCredit answers without an installment plan, e.g. for an amount outside the offered plans or on an error response. `getInstallmentPlanV3()` in `Application/Component/Widget/EasyCreditExampleCalculation.php` read `installmentPlans[0]->plans` unchecked; it now returns `null` in that case, so `hasExampleCalculation()` is `false` and the page renders without the example calculation. Covered by two new unit tests. The same fix was made in the OXID 7 module (branch `b-7.0.x`).

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

## [3.0.9] - 2025-03-04

### Fixed

- [0007754](https://bugs.oxid-esales.com/view.php?id=7754): fix ModuleChainGenerator that has issue loading EasyCreditPayment

## [3.0.8] - 2022-09-08

### Changed

- Rebranding easyCredit-Ratenkauf

## [3.0.7] - 2022-02-28

### Fixed

- Bugfix release

## [3.0.6] - 2022-02-08

### Changed

- Improve backwards compatibility to PHP 7.2
- Calculate the installment plan only within the payment price range (by default 200 < x < 10000)

## [3.0.5] - 2022-01-25

### Changed

- Remove payment costs in checkout
- Add better default values for payment

## [3.0.4] - 2021-12-17

### Changed

- Transfer order number to easyCredit
- Remove "Ankaufsobergrenze"

## [3.0.3] - 2021-11-19

### Fixed

- Bugfixes

## [3.0.2] - 2021-11-16

### Fixed

- Bugfixes

## [3.0.1] - 2021-11-02

### Fixed

- Bugfixes

## [3.0.0] - 2021-10-11

### Added

- Integrate new API for dealer gateway
- Transaction overview in admin backend
- Cancellation (storno) in admin backend

### Changed

- Introduce namespaces
- No more support for OXID <= 6.0

## [2.0.6] - 2021-07-16

### Fixed

- Elimination of malfunctions in other payment modules

## [2.0.5] - 2021-07-14

### Changed

- Birthday is not required
- Possibility to use own jQuery UI library in frontend

## [2.0.4] - 2020-12-11

### Changed

- Function check for OXID 6.2.3
- easyCredit orders are not changeable (discounts, adding articles, ...) in OXID admin backend

## [2.0.0] - 2020-04-30

### Changed

- Version for OXID 6 installable via Composer

## [1.0.0]

- Version for OXID 4 installable via FTP
