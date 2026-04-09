# Change Log for easyCredit for OXID

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).


## [4.0.2] - 2026-04-09

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
- Replace MD5 with SHA-256 for payment integrity hash in EasyCreditInitializeRequestBuilder
- Add SECURITY.md documenting known security considerations and intentionally unfixed items

## [4.0.1] - 2025-10-02

### FIXED

- use ModuleSettings-Container not in the constructor of the viewConfig
- Telephone-Validation in easyCredit-Wallet, not in OXID