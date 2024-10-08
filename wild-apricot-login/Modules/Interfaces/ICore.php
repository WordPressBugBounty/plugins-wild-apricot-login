<?php

interface WA_Modules_Interfaces_ICore extends WA_Modules_Interfaces_IModule
{
    const WA_CONTACT_DATA_LOADED = 'wa_integration_wa_contact_loaded';
    const GET_WA_LOGIN_FORM = 'wa_login_form';

    public function getModuleOption($moduleId, $optionKey, $forceLoad = false);
    public function setModuleOption($moduleId, $optionKey, $optionValue);
    public function unsetModuleOption($moduleId, $optionKey);
    public function saveOptions();

    public function getModuleSessionData($moduleId, $key);
    public function setModuleSessionData($moduleId, $key, $value);
    public function unsetModuleSessionData($moduleId, $key);

    public function getWaAccount();
    public function isOAuth2SettingsValid($clientId, $secret);
    public function getOAuth2Token($code, $clientId, $secret, $redirectUri);
    public function actualizeOAuth2Token($oAuthToken, $clientId, $secret);
    public function getWpUserByWaOAuth2Token($oAuth2Token);
    public function getWpUserByWaContact($waContact);
    public function getWaRoleId($waLevelId);
    public function getWaUserLogin($waContactId);
    public function sendWaApiRequest($path, $method = 'GET', array $params = null);

    public function getWaDefaultRole();
    public function getWaLoginForm(array $attr = array());
}