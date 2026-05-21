<?php

namespace OxidSolutionCatalysts\EasyCredit\Core;

use OxidSolutionCatalysts\EasyCredit\Traits\EasyCreditServiceContainer;
use OxidSolutionCatalysts\EasyCredit\Service\EasyCreditModuleSettings;

class EasyCreditViewConfig extends EasyCreditViewConfig_parent
{
    use EasyCreditServiceContainer;

    /** @var EasyCreditModuleSettings $moduleSettings */
    protected $moduleSettings;

    protected function getEasyCreditModuleSettings()
    {
        if (!$this->moduleSettings) {
            $this->moduleSettings = $this->getServiceFromContainer(EasyCreditModuleSettings::class);
        }
        return $this->moduleSettings;
    }

    public function getOxpsECExampleCalcBasket()
    {
        return $this->getEasyCreditModuleSettings()->getOxpsECExampleCalcBasket();
    }

    public function getOxpsECExampleCalcMinibasket()
    {
        return $this->getEasyCreditModuleSettings()->getOxpsECExampleCalcMinibasket();
    }

    public function getOxpsECExampleCalcArticle()
    {
        return $this->getEasyCreditModuleSettings()->getOxpsECExampleCalcArticle();
    }
}
