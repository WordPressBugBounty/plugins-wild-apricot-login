<?php

interface WA_Modules_Interfaces_ISessionManager
{
    public function get($moduleId, $key);
    public function set($moduleId, $key, $value);
    public function remove($moduleId, $key);
}