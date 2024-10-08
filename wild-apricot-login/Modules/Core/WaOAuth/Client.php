<?php

class WA_Modules_Core_WaOAuth_Client
{
    const TOKEN_OPTION_ID = 'OAuthToken';
    const TOKEN_REQUEST_PATH = '/auth/token';
    const TOKEN_API_KEY_ID = 'APIKEY';
    const TOKEN_SCOPE = 'auto';
    const TOKEN_GRANT_TYPE = 'client_credentials';
    const TOKEN_REFRESH_GRANT_TYPE = 'refresh_token';
    const TOKEN_LOCK_ID = 'wa_integration_token_lock';
    const TOKEN_LOCK_TIME = 10;

    const OAUTH2_TOKEN_GRANT_TYPE = 'authorization_code';
    const OAUTH2_TOKEN_SCOPE = 'contacts_me';

    const OAUTH2_VALIDATION_GRANT_TYPE = 'password';
    const OAUTH2_VALIDATION_USERNAME = 'wildapricot@wildapricot.com';
    const OAUTH2_VALIDATION_VALID_STATE = 'invalid_grant';

    private $module;
    private $url;
    private $token;

    public function __construct(WA_Modules_Interfaces_IModule $module, $url)
    {
        $this->module = $module;
        $this->url = untrailingslashit($url);
        $this->token = $module->getOption(self::TOKEN_OPTION_ID);
    }

    public function isOAuth2SettingsValid($clientId, $secret)
    {
        $tokenResponse = $this->requestToken(
            $clientId,
            $secret,
            array
            (
                'grant_type' => self::OAUTH2_VALIDATION_GRANT_TYPE,
                'username' => self::OAUTH2_VALIDATION_USERNAME,
                'password' => wp_generate_password(),
                'scope' => self::OAUTH2_TOKEN_SCOPE
            )
        );

        if (!WA_Utils::isNotEmptyArray($tokenResponse) || empty($tokenResponse['body']))
        {
            WA_Error_Handler::handleError('wa_integration_invalid_settings_check_response', 'Unable to get valid response for OAuth2 settings validation.');
            return false;
        }

        $responseData = json_decode($tokenResponse['body']);

        if (is_object($responseData) && isset($responseData->error) && $responseData->error == self::OAUTH2_VALIDATION_VALID_STATE)
        {
            return true;
        }

        WA_Error_Handler::handleError('wa_integration_invalid_OAuth2_settings', 'OAuth2 settings is invalid.');
        return false;
    }

    public function getOAuth2Token($code, $clientId, $secret, $redirectUri)
    {
        $tokenResponse = $this->requestToken(
            $clientId,
            $secret,
            array
            (
                'grant_type' => self::OAUTH2_TOKEN_GRANT_TYPE,
                'code' => $code,
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => self::OAUTH2_TOKEN_SCOPE
            )
        );

        $token = new WA_Modules_Core_WaOAuth_Token('', $tokenResponse);

        if (!$token->isValid())
        {
            return WA_Error_Handler::handleError('wa_integration_oauth2_error', 'Unable to get OAuth2 token.');
        }

        return $token;
    }

    public function actualizeOAuth2Token($oAuthToken, $clientId, $secret)
    {
        if (!($oAuthToken instanceof WA_Modules_Core_WaOAuth_Token) || !$oAuthToken->isValid())
        {
            return WA_Error_Handler::handleError('wa_integration_oauth2_error', 'OAuth token is invalid.');
        }

        if (!$oAuthToken->isExpired())
        {
            return $oAuthToken;
        }

        $oAuthToken = $this->refreshOAuth2Token($oAuthToken, $clientId, $secret);

        return $oAuthToken;
    }

    public function isTokenValid()
    {
        return !empty($this->token) && $this->token->isValid();
    }

    public function getAuthorizationHeader()
    {
        if (!$this->isTokenValid())
        {
            return WA_Error_Handler::handleError('wa_integration_oauth2_error', 'OAuth token is invalid.');
        }

        if ($this->token->isExpired())
        {
            $this->refreshToken();

            if ($this->token->isExpired())
            {
                return WA_Error_Handler::handleError('wa_integration_oauth2_error', 'Unable to refresh OAuth token.');
            }
        }

        return $this->token->getAuthorizationHeader();
    }

    public function setToken($apiKey)
    {
        $this->token = new WA_Modules_Core_WaOAuth_Token(
            $apiKey,
            $this->requestToken(
                self::TOKEN_API_KEY_ID,
                $apiKey,
                array('grant_type' => self::TOKEN_GRANT_TYPE, 'scope' => self::TOKEN_SCOPE)
            )
        );

        $this->module->setOption(self::TOKEN_OPTION_ID, $this->token);
    }

    public function reloadToken()
    {
        $this->token = $this->module->getOption(self::TOKEN_OPTION_ID, true);
    }

    private function refreshOAuth2Token($oAuth2Token, $clientId, $secret)
    {
        $lockId = self::TOKEN_LOCK_ID . '_' . wp_hash_password($oAuth2Token->getRefreshToken());

        WA_Lock_Provider::wait($lockId, self::TOKEN_LOCK_TIME);
        WA_Lock_Provider::acquire($lockId, self::TOKEN_LOCK_TIME);

        $oAuth2Token = new WA_Modules_Core_WaOAuth_Token(
            '',
            $this->requestToken
            (
                $clientId,
                $secret,
                array('grant_type' => self::TOKEN_REFRESH_GRANT_TYPE, 'refresh_token' => $oAuth2Token->getRefreshToken())
            )
        );

        WA_Lock_Provider::release($lockId);

        if (!($oAuth2Token instanceof WA_Modules_Core_WaOAuth_Token) || !$oAuth2Token->isValid())
        {
            return WA_Error_Handler::handleError('wa_integration_oauth2_error', 'Unable to refresh OAUth token.');
        }

        return $oAuth2Token;
    }

    private function refreshToken()
    {
        WA_Lock_Provider::wait(self::TOKEN_LOCK_ID, self::TOKEN_LOCK_TIME, array($this, 'reloadToken'));
        WA_Lock_Provider::acquire(self::TOKEN_LOCK_ID, self::TOKEN_LOCK_TIME);

        $newToken = new WA_Modules_Core_WaOAuth_Token(
            $this->token->getApiKey(),
            $this->requestToken
            (
                self::TOKEN_API_KEY_ID,
                $this->token->getApiKey(),
                array('grant_type' => self::TOKEN_REFRESH_GRANT_TYPE, 'refresh_token' => $this->token->getRefreshToken())
            )
        );

        if (!$newToken->isValid())
        {
            $newToken = new WA_Modules_Core_WaOAuth_Token(
                $this->token->getApiKey(),
                $this->requestToken(
                    self::TOKEN_API_KEY_ID,
                    $this->token->getApiKey(),
                    array('grant_type' => self::TOKEN_GRANT_TYPE, 'scope' => self::TOKEN_SCOPE)
                )
            );
        }

        if ($newToken->isValid())
        {
            $this->token = $newToken;
            $this->module->setOption(self::TOKEN_OPTION_ID, $this->token);
            $this->module->saveOptions();
        }

        WA_Lock_Provider::release(self::TOKEN_LOCK_ID);
    }

    private function requestToken($username, $password, array $params)
    {
        $httpClient = new WA_Http_Client();

        return $httpClient->sendRequest
        (
            $this->url . self::TOKEN_REQUEST_PATH,
            array
            (
                'method' => 'POST',
                'headers' => array
                (
                    'Authorization' => 'Basic ' . base64_encode($username . ':' . $password)
                ),
                'body' => $params
            )
        );
    }
}