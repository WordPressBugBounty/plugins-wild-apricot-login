<?php

class WA_Modules_Core_Options extends WA_Modules_Base_Options
{
    protected function update($options)
    {
        return update_option($this->optionName, $options);
    }

    protected function load()
    {
        return get_option($this->optionName);
    }
}