<?php

namespace OxidSolutionCatalysts\EasyCredit\Controller\Admin;

use OxidSolutionCatalysts\EasyCredit\Core\Events;

class EasyCreditModuleConfigurationController extends EasyCreditModuleConfigurationController_parent
{
    public function saveConfVars()
    {
        parent::saveConfVars();

        if ($this->getEditObjectId() === 'osceasycredit') {
            Events::onModuleSettingsSaved();
        }
    }
}
