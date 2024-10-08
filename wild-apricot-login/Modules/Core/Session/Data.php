<?php

class WA_Modules_Core_Session_Data extends WA_Modules_Base_Options
{
    private $expirationTime;

    public function __construct($optionName, $expirationTime)
    {
        parent::__construct($optionName, false, true);
        $this->expirationTime = $expirationTime;
    }

    public function renew()
    {
        $this->save(true);
    }

    protected function update($options)
    {
        return set_transient($this->optionName, $options, $this->expirationTime);
    }

    protected function load()
    {
        return get_transient($this->optionName);
    }
}