# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- GitHub actions for building project
- Validation on first and last name according to EasyCredit model
- autoload-dev to composer file for OXID devs to test locally
- phpstan checks
- phpcs checks and fixes

### Removed

- Change log removed from README.md
- Static call in [EasyCreditPayment](Core/Domain/EasyCreditPayment.php)

### Fixed

- Undefined variable for PHP 8+ warnings
- Undefined object in PHP 8+

## 3.0.8 - 2022-09-08
- Rebranding easyCredit-Ratenkauf

## 3.0.7
- Bugfix-release

### 3.0.6
- Improve Backwards-compatibility to PHP7.2
- Calculate Rateplan only in Payment-Pricerange (by default 200 < x < 10000)

## 3.0.5
- Remove Payment-Costs in Checkout
- Add better default-values for payment

## 3.0.4
- Transfer OrderNr to EasyCredit
- Remove Ankaufsobergrenze

## 3.0.3
- Bugfixes

## 3.0.2
- Bugfixes

## 3.0.1
- Bugfixes

## 3.0.0
- Introduce Namespaces
- No more support for OXID <= v6.0
- Integrate new API für dealer gateway
- Transaction-Overview in Backend
- Storno in Backend

## 2.0.6
- Fix: Elimination of malfunctions in other payment modules

## 2.0.5
- Birthday is not required
- Possibility to use own jqueryUI-Lib in Frontend

## 2.0.4
- Function-Check for OXID 6.2.3
- easyCredit Orders are not changable (Discounts, add Articles...) in OXID-Backend

## 2.0.0
- Version for OXID6 installable via Composer

## 1.0.0
- Version for OXID4 installable via FTP


[unreleased]: https://github.com/OXIDprojects/easycredit-module/compare/v3.0.8...HEAD