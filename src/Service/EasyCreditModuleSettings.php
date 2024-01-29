<?php


/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\EasyCredit\Service;


use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;

class EasyCreditModuleSettings
{
    private ModuleSettingServiceInterface $moduleSettingService;

    public function __construct(
        ModuleSettingServiceInterface $moduleSettingService
    ) {
        $this->moduleSettingService = $moduleSettingService;
    }

    public function getOxpsECExampleCalcBasket() : bool {
        return $this->moduleSettingService->getBoolean('oxpsECExampleCalcBasket', 'osceasycredit');
    }

    public function getOxpsECExampleCalcMinibasket() {
        return $this->moduleSettingService->getBoolean('oxpsECExampleCalcMinibasket', 'osceasycredit');
    }

    public function getOxpsECExampleCalcArticle() {
        return $this->moduleSettingService->getBoolean('oxpsECExampleCalcArticle', 'osceasycredit');
    }

    public function getOxpsECBaseUrl() {
        return (string) $this->moduleSettingService->getString('oxpsECBaseUrl', 'osceasycredit');
    }

    public function getOxpsECDealerInterfaceUrl() {
        return (string) $this->moduleSettingService->getString('oxpsECDealerInterfaceUrl', 'osceasycredit');
    }

    public function getOxpsECWebshopId() {
        return (string) $this->moduleSettingService->getString('oxpsECWebshopId', 'osceasycredit');
    }

    public function getOxpsECWebshopToken() {
        return (string) $this->moduleSettingService->getString('oxpsECWebshopToken', 'osceasycredit');
    }

    public function getOxpsECLogging() {
        return $this->moduleSettingService->getBoolean('oxpsECLogging', 'osceasycredit');
    }

    public function getOxpsECCheckoutValidConfirm() {
        return $this->moduleSettingService->getBoolean('oxpsECCheckoutValidConfirm', 'osceasycredit');
    }

    public function getOxpsECExampleUseOwnjQueryUI() {
        return $this->moduleSettingService->getBoolean('oxpsECExampleUseOwnjQuery', 'osceasycredit');
    }
}