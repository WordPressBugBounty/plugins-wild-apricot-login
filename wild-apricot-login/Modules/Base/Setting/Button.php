<?php

class WA_Modules_Base_Setting_Button extends WA_Modules_Base_Setting_Base
{
    public function render()
    {
        echo '<input type="button" class="button button-small wa_integration_settings_button"'
            . ' id="' . $this->getFieldId() . '"'
            . ' value="' . esc_attr($this->args['buttonTitle']) . '"'
            . ($this->module->isSettingsValid() ? '' : ' disabled')
            . ' />';

        $this->renderDescription();
    }

    public function updateSetting($setting) {}

    protected function getSpinnerId()
    {
        return $this->getFieldId() . '_spinner';
    }

    protected function renderDescription()
    {
        echo '<div><div id="' . $this->getSpinnerId() . '" class="spinner wa_integration_settings_button_spinner"></div>';
        echo '<p class="description" id="' .$this->getDescriptionId() . '">';
        echo (isset($this->args['description']) && WA_Utils::isNotEmptyString($this->args['description']))
            ? $this->args['description']
            : '&nbsp;';
        echo '</p></div>';
    }
}