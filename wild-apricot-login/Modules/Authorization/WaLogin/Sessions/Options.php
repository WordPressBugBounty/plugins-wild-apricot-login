<?php

class WA_Modules_Authorization_WaLogin_Sessions_Options extends WA_Modules_Base_Options
{
    private $userId;

    public function __construct($userId, $optionName)
    {
        parent::__construct($optionName);

        $this->userId = $userId;
    }

    public function getUnitKeys()
    {
        $this->loadOptions(true);

        return array_keys($this->options);
    }

    public function unsetUnit($unit, $forceLoad = false)
    {
        $this->loadOptions($forceLoad);

        $unit = WA_Utils::sanitizeKey($unit);

        if (isset($this->options[$unit]))
        {
            unset($this->options[$unit]);
            $this->isOptionsChanged = true;
        }
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