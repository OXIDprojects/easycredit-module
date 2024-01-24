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
}