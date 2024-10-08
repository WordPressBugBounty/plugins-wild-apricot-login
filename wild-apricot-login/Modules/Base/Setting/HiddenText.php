<?php

class WA_Modules_Base_Setting_HiddenText extends WA_Modules_Base_Setting_Text
{
    const PLACEHOLDER_LENGTH = 10;

    public function render()
    {
        if ($this->isPlaceholderRequired())
        {
            $this->args['placeholder'] = str_pad('', self::PLACEHOLDER_LENGTH, '*');
        }

        echo '<script>try { new WaHiddenTextField({ fieldId: "' . $this->getFieldId() . '", descriptionId: "' . $this->getDescriptionId() . '"}); } catch (e) {}</script>';
        parent::render();
    }

    public function updateSetting($setting)
    {
        if (WA_Utils::isNotEmptyString($setting))
        {
            parent::updateSetting($setting);
        }
    }

    protected function getFieldValue()
    {
        return '';
    }

    protected function isPlaceholderRequired()
    {
        $fieldValue = parent::getFieldValue();

        return !empty($fieldValue);
    }
}