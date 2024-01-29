<?php

namespace OxidSolutionCatalysts\EasyCredit\Core;

use OxidSolutionCatalysts\EasyCredit\Traits\EasyCreditServiceContainer;
use OxidSolutionCatalysts\EasyCredit\Service\EasyCreditModuleSettings;

class EasyCreditViewConfig extends EasyCreditViewConfig_parent
{
    use EasyCreditServiceContainer;

    /** @var EasyCreditModuleSettings $moduleSettings */
    protected $moduleSettings;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->moduleSettings = $this->getServiceFromContainer(EasyCreditModuleSettings::class);
    }

    public function getOxpsECExampleCalcBasket() {
        return $this->moduleSettings->getOxpsECExampleCalcBasket();
    }

    public function getOxpsECExampleCalcMinibasket() {
        return $this->moduleSettings->getOxpsECExampleCalcMinibasket();
    }

    public function getOxpsECExampleCalcArticle() {
        return $this->moduleSettings->getOxpsECExampleCalcArticle();
    }
}