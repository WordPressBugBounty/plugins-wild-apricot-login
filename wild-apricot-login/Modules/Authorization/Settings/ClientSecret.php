<?php

class WA_Modules_Authorization_Settings_ClientSecret extends WA_Modules_Base_Setting_HiddenText
{
    public function __construct(WA_Modules_Interfaces_IAuthorization $module, $optionKey, $title = null, array $args = array())
    {
        parent::__construct($module, $optionKey, $title, $args);
    }

    public function render()
    {
        $this->args['description'] = $this->module->isSettingsValid()
            ? __('Client secret is set.', WaIntegrationPlugin::TEXT_DOMAIN)
            : __('Client secret is empty or invalid.', WaIntegrationPlugin::TEXT_DOMAIN);

        parent::render();
    }

    public function updateSetting($setting)
    {
        parent::updateSetting($setting);
        $this->module->updateOAuth2Settings();
    }
}