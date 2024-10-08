<?php

interface WA_Modules_Interfaces_ISynchronizer extends WA_Modules_Interfaces_IModule
{
    public function syncWaRoles($waLevels);
    public function getWaLevels();
    public function syncWaContact($waContact);
    public function getWpUserByWaContact($waContact);
    public function getWaDefaultRole();
    public function getWaRoleId($waLevelId);
    public function isWaLevelValid($waLevel);
    public function getWaUserLogin($waContactId);
}