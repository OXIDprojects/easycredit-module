<?php

namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;

use OxidProfessionalServices\EasyCredit\Core\Events;

class EasyCreditModuleConfiguration extends EasyCreditModuleConfiguration_parent
{
    public function easycreditIsApiKeyUsable()
    {
        Events::onModuleSettingsSaved();
    }
    
    public function easycreditHasV3ApiKeys() {
    }
}
