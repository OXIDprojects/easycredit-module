<?php

namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;

use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Events;
use OxidEsales\Eshop\Core\Registry;

class EasyCreditModuleConfiguration extends EasyCreditModuleConfiguration_parent
{

    public function render()
    {
        $apiConfig = EasyCreditDicFactory::getDic()->getApiConfig();
        if ($apiConfig->getEasyCreditUseApiVersionV3()) {
            $currentClass = Registry::getConfig()->getRequestParameter('cl');
            $fnc = Registry::getConfig()->getRequestParameter('fnc');

            if ($currentClass === 'module_config' && $fnc === 'save') {
                $message = Events::checkEasyCreditCredentials() ? 'Die easyCredit Zugangsdaten für die API V3 konnten erfolgreich geprüft werden.' : 'Die easyCredit Zugangsdaten konnten nicht erfolgreich geprüft werden. Bitte prüfen Sie Webshop-ID, Token, HMAC Secret und API-URL V3.';
                $this->_aViewData['validateEcCredentials'] = $message;
            }
        }
        return parent::render();
    }

    public function easycreditIsApiKeyUsable()
    {
        $apiConfig = EasyCreditDicFactory::getDic()->getApiConfig();
        if ($apiConfig->getEasyCreditUseApiVersionV3()) {
            return Events::checkEasyCreditCredentials();
        }
    }
    
    public function easycreditIsApiV3() {
        $apiConfig = EasyCreditDicFactory::getDic()->getApiConfig();
        return $apiConfig->getEasyCreditUseApiVersionV3();
    }
}
