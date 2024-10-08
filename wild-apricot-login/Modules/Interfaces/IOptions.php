<?php

interface WA_Modules_Interfaces_IOptions
{
    public function getOption($unit, $key, $forceLoad = false);
    public function setOption($unit, $key, $value, $forceLoad = false);
    public function unsetOption($unit, $key, $forceLoad = false);
    public function save();
}