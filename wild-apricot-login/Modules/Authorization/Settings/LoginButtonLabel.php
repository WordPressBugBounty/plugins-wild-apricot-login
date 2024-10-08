<?php

class WA_Modules_Authorization_Settings_LoginButtonLabel extends WA_Modules_Base_Setting_Text
{
    public function __construct(WA_Modules_Interfaces_IAuthorization $module, $optionKey, $title = null, array $args = array())
    {
        parent::__construct($module, $optionKey, $title, $args);
    }

    public function getFieldValue()
    {
        return $this->module->getDefaultLoginLabel();
    }
}