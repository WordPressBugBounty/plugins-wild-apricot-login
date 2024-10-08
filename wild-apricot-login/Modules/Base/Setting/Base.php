<?php

class WA_Modules_Base_Setting_Base implements WA_Modules_Interfaces_ISetting
{
    protected $module;
    protected $optionName;
    protected $optionKey;
    protected $title;
    protected $args;

    public function __construct(WA_Modules_Interfaces_IModule $module, $optionKey, $title = null, array $args = array())
    {
        $this->module = $module;
        $this->optionKey = $optionKey;
        $this->title = $title;
        $this->args = $args;

        add_action(WA_Modules_Interfaces_ISettingsManager::SETTINGS_REGISTER_HOOK, array($this, 'onSettingsRegister'));
        add_action(WA_Modules_Interfaces_ISettingsManager::SETTINGS_ENQUEUE_SCRIPT_HOOK, array($this, 'enqueueScript'));
        add_action(WA_Modules_Interfaces_ISettingsManager::SETTINGS_RENDER_HOOK, array($this, 'onSettingsPageRender'));
        add_action(WA_Modules_Interfaces_ISettingsManager::SETTINGS_UPDATE_HOOK, array($this, 'onSettingsUpdate'), 10, 2);
    }

    public function onSettingsRegister(WA_Modules_Interfaces_ISettingsManager $settingsManager)
    {
        $this->optionName = $settingsManager->getOptionName();
    }

    public function onSettingsPageRender(WA_Modules_Interfaces_ISettingsManager $settingsManager)
    {
        if (!current_user_can('manage_options')) { return; }

        $pageId = $settingsManager->getPageId();
        $sectionId = !empty($this->args['sectionId']) ? $this->args['sectionId'] : $this->module->getId();
        $sectionTitle = !empty($this->args['sectionTitle']) ? $this->args['sectionTitle'] : null;

        add_settings_section($sectionId, $sectionTitle, array($this, 'renderSection'), $pageId);
        add_settings_field(
            $this->optionKey,
            $this->title,
            array($this, 'render'),
            $pageId,
            $sectionId,
            array('label_for' => $this->getFieldId())
        );
    }

    public function onSettingsUpdate(array $settingsToUpdate, WA_Modules_Interfaces_ISettingsManager $settingsManager)
    {
        if (!current_user_can('manage_options')) { return; }

        $moduleId = $this->module->getId();
        $optionKey = $this->optionKey;

        $this->updateSetting(
            (isset($settingsToUpdate[$moduleId]) && isset($settingsToUpdate[$moduleId][$optionKey])) ? $settingsToUpdate[$moduleId][$optionKey] : null
        );
		
		$settingsManager->onSettingsUpdate($moduleId, $this->module->isSettingsValid());
    }

    public function enqueueScript() {}
    public function renderSection() {}

    public function render()
    {
        return '';
    }

    public function updateSetting($setting)
    {
        throw new Exception('You must implement "updateSetting" method.');
    }

    protected function getFieldId()
    {
        return implode('_', array('id', $this->optionName, $this->module->getId(), $this->optionKey));
    }

    protected function getFieldName()
    {
        return $this->optionName . '[' . $this->module->getId() . ']' . '[' . $this->optionKey . ']';
    }

    protected function getDescriptionId()
    {
        return $this->getFieldId() . '_description';
    }

    protected function getFieldValue()
    {
        return $this->module->getOption($this->optionKey);
    }

    protected function renderDescription()
    {
        if (!isset($this->args['description']) || !WA_Utils::isNotEmptyString($this->args['description'])) { return; }
		
		$descriptionCssClass = $this->module->isSettingsValid() ? '' : 'error-message';

        echo '<p class="description" id="' .$this->getDescriptionId() . '"><span class="' . $descriptionCssClass . '" style="font-weight: normal;">' . $this->args['description'] . '</span></p>';
    }
}