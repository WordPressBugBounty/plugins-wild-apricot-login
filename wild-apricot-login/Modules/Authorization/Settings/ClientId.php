<?php

class WA_Modules_Authorization_Settings_ClientId extends WA_Modules_Base_Setting_HiddenText
{
    public function render()
    {
        $this->args['description'] = $this->module->isSettingsValid()
            ? __('Client ID is set.', WaIntegrationPlugin::TEXT_DOMAIN)
            : __('Client ID is empty or invalid.', WaIntegrationPlugin::TEXT_DOMAIN);

        parent::render();
    }
}