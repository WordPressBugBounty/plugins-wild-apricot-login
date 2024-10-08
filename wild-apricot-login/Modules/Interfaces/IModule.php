<?php

interface WA_Modules_Interfaces_IModule
{
    public function getId();
    public function getName();
    public function activate(WA_Modules_Interfaces_ICore $core, array $args = null);

    public function getOption($optionKey, $forceLoad = false);
    public function setOption($optionKey, $optionValue);
    public function unsetOption($optionKey);

    public function getSessionData($key);
    public function setSessionData($key, $value);
    public function unsetSessionData($key);

    public function enqueueScript($scriptName);
    public function enqueueStyle($styleName);

    public function isSettingsValid();
}