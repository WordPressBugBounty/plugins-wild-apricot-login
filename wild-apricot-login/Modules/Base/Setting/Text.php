<?php

class WA_Modules_Base_Setting_Text extends WA_Modules_Base_Setting_Base
{
    const BASE_FIELD_CSS_CLASS = 'regular-text';
    const DEFAULT_MAX_LENGTH = 255;

    public function render()
    {
        $cssClass = self::BASE_FIELD_CSS_CLASS
            . ((isset($this->args['cssClass']) && WA_Utils::isNotEmptyString($this->args['cssClass'])) ? ' ' . $this->args['cssClass'] : '');

        $placeholder = ((isset($this->args['placeholder']) && WA_Utils::isNotEmptyString($this->args['placeholder'])) ? $this->args['placeholder'] : '');

        echo '<input type="text"'
            . ' name="' . esc_attr($this->getFieldName()) . '"'
            . ' id="' . esc_attr($this->getFieldId()) . '"'
            . ' value="' . esc_attr($this->getFieldValue()) . '"'
            . ' class="' . esc_attr($cssClass) . '"'
            . ' maxlength="'. esc_attr($this->getMaxLength()) . '"'
            . (($placeholder == '') ? '' : ' placeholder="'. esc_attr($placeholder) . '"')
            . ' />';

        $this->renderDescription();
    }

    public function updateSetting($setting)
    {
       $this->module->setOption
       (
           $this->optionKey,
           $this->sanitizeSetting($setting)
       );
    }

    protected function sanitizeSetting($setting)
    {
        return WA_Utils::sanitizeString($setting, $this->getMaxLength());
    }

    private function getMaxLength()
    {
        return (isset($this->args['maxLength']) && is_int($this->args['maxLength']) && $this->args['maxLength'] > 0) ? $this->args['maxLength'] : self::DEFAULT_MAX_LENGTH;
    }
}