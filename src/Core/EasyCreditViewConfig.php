<?php

namespace OxidSolutionCatalysts\EasyCredit\Core;

use OxidSolutionCatalysts\EasyCredit\Traits\EasyCreditServiceContainer;
use OxidSolutionCatalysts\EasyCredit\Service\EasyCreditModuleSettings;

class EasyCreditViewConfig extends EasyCreditViewConfig_parent
{
    use EasyCreditServiceContainer;

    /** @var EasyCreditModuleSettings $moduleSettings */
    protected $moduleSettings;

    protected function getModuleSettings()
    {
        if (!$this->moduleSettings) {
            $this->moduleSettings = $this->getServiceFromContainer(EasyCreditModuleSettings::class);
        }
        return $this->moduleSettings;
    }

    public function getOxpsECExampleCalcBasket()
    {
        return $this->getModuleSettings()->getOxpsECExampleCalcBasket();
    }

    public function getOxpsECExampleCalcMinibasket()
    {
        return $this->getModuleSettings()->getOxpsECExampleCalcMinibasket();
    }

    public function getOxpsECExampleCalcArticle()
    {
        return $this->getModuleSettings()->getOxpsECExampleCalcArticle();
    }
}
