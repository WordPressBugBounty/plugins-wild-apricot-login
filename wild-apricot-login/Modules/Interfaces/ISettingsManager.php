<?php

interface WA_Modules_Interfaces_ISettingsManager extends WA_Modules_Interfaces_IModule
{
    const SETTINGS_REGISTER_HOOK = 'wa_integration_settings_register';
    const SETTINGS_ENQUEUE_SCRIPT_HOOK = 'wa_integration_settings_enqueue_script';
    const SETTINGS_RENDER_HOOK = 'wa_integration_settings_render';
    const SETTINGS_UPDATE_HOOK = 'wa_integration_settings_update';

    public function getPageId();
    public function getOptionName();
    public function notifySettingsRegister();
    public function notifyEnqueueScript($hook);
    public function notifySettingsPageRender();
    public function notifySettingsUpdate(array $settingsToUpdate);
	public function onSettingsUpdate($moduleId, $isValid);
}