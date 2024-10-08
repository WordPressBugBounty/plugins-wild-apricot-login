<?php

interface WA_Modules_Interfaces_ISetting
{
    public function onSettingsRegister(WA_Modules_Interfaces_ISettingsManager $settingsManager);
    public function enqueueScript();
    public function onSettingsPageRender(WA_Modules_Interfaces_ISettingsManager $settingsManager);
    public function renderSection();
    public function render();
    public function onSettingsUpdate(array $settingsToUpdate, WA_Modules_Interfaces_ISettingsManager $settingsManager);
    public function updateSetting($setting);
}