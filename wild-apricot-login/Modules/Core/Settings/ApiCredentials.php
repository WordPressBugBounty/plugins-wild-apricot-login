<?php

class WA_Modules_Core_Settings_ApiCredentials extends WA_Modules_Base_Setting_HiddenText
{
    public function __construct(WA_Modules_Interfaces_ICore $module, $optionKey, $title = null, array $args = array())
    {
        parent::__construct($module, $optionKey, $title, $args);
    }

    public function render()
    {
        $this->args['description'] = $this->module->isSettingsValid()
            ? __('API key is set. Account ID is ', WaIntegrationPlugin::TEXT_DOMAIN) . $this->module->getWaAccount()->getId()
            : __('API key is empty or invalid.', WaIntegrationPlugin::TEXT_DOMAIN);

        parent::render();
    }

    public function updateSetting($setting)
    {
        $apiKey = parent::sanitizeSetting($setting);

        if (WA_Utils::isNotEmptyString($apiKey))
        {
            $this->module->setOAuthToken($apiKey);
            $this->module->setWaAccount();
        }
    }

    protected function isPlaceholderRequired()
    {
        return $this->module->isSettingsValid();
    }
} 