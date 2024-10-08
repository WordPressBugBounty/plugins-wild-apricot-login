<?php

interface WA_Modules_Interfaces_IAuthorization extends WA_Modules_Interfaces_IModule
{
    public function updateOAuth2Settings();
    public function getOAuth2Token($code, $redirectUri);
    public function getWpUserByWaOAuth2Token($oAuth2Token);
    public function getOAuth2ClientId();
    public function getOAuth2Secret();
    public function actualizeOAuth2Token($oAuthToken, $clientId, $secret);
    public function getDefaultLoginLabel();
}