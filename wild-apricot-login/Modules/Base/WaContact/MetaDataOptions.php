<?php

class WA_Modules_Base_WaContact_MetaDataOptions extends WA_Modules_Base_Options
{
    private $userId;

    public function __construct($optionName, $userId)
    {
        parent::__construct($optionName, false, false);
        $this->userId = $userId;
    }

    protected function update($options)
    {
        return update_user_meta($this->userId, $this->optionName, $options);
    }

    protected function load()
    {
        return get_user_meta($this->userId, $this->optionName, true);
    }
}