<?php

class WA_Modules_Core_WaApi_Client
{
    const ACCOUNT_OPTION_ID = 'ApiAccount';
    const ACCOUNT_REQUEST_PATH = '/Accounts';
    const CONTACT_PATH = '/contacts/me';

    private $module;
    private $oAuthClient;
    private $url;
    private $account;

    public function __construct(WA_Modules_Interfaces_IModule $module, $oAuthClient, $url)
    {
        $this->module = $module;
        $this->oAuthClient = $oAuthClient;
        $this->url = untrailingslashit($url);
        $this->account = $module->getOption(self::ACCOUNT_OPTION_ID);
    }

    public function getWaContactByOAuth2Token($oAuth2Token)
    {
        if (is_wp_error($oAuth2Token))
        {
            return $oAuth2Token;
        }

        if (!$this->isAccountValid())
        {
            return WA_Error_Handler::handleError('wa_integration_api_error', 'API account is invalid.');
        }

        $authHeader = $oAuth2Token->getAuthorizationHeader();
        $httpClient = new WA_Http_Client();

        $response = $httpClient->sendRequest
        (
            $this->account->getUrl() . self::CONTACT_PATH,
            array
            (
                'method' => 'GET',
                'headers' => array
                (
                    'Content-Type' => 'application/json',
                    'Authorization' => $authHeader
                )
            )
        );

        if (is_wp_error($response))
        {
            return $response;
        }

        if (!is_array($response) || !isset($response['body']))
        {
            return WA_Error_Handler::handleError('wa_integration_api_error', 'Unable to perform API request.');
        }

        return json_decode($response['body']);
    }

    public function sendApiRequest($path, $method = 'GET', array $params = null)
    {
        $authHeader = $this->oAuthClient->getAuthorizationHeader();

        if (is_wp_error($authHeader))
        {
            return $authHeader;
        }

        if (!$this->isAccountValid())
        {
            return WA_Error_Handler::handleError('wa_integration_api_error', 'API account is invalid.');
        }

        $httpClient = new WA_Http_Client();

        $response = $httpClient->sendRequest
        (
            $this->account->getUrl() . $path,
            array
            (
                'method' => $method,
                'headers' => array
                (
                    'Content-Type' => 'application/json',
                    'Authorization' => $authHeader
                ),
                'body' => $params
            )
        );

        if (is_wp_error($response))
        {
            return $response;
        }

        if (!is_array($response) || !isset($response['body']))
        {
            return WA_Error_Handler::handleError('wa_integration_api_error', 'Unable to perform API request.');
        }

        return json_decode($response['body']);
    }

    public function isAccountValid()
    {
        return !empty($this->account) && $this->account->isValid();
    }

    public function getAccount()
    {
        return $this->isAccountValid() ? $this->account : null;
    }

    public function setAccount()
    {
        $this->account = new WA_Modules_Core_WaApi_Account($this->requestAccountData());
        $this->module->setOption(self::ACCOUNT_OPTION_ID, $this->account);
    }

    private function requestAccountData()
    {
        $authHeader = $this->oAuthClient->getAuthorizationHeader();

        if (is_wp_error($authHeader))
        {
            return $authHeader;
        }

        $httpClient = new WA_Http_Client();

        return $httpClient->sendRequest
        (
            $this->url . self::ACCOUNT_REQUEST_PATH,
            array
            (
                'method' => 'GET',
                'headers' => array
                (
                    'Content-Type' => 'application/json',
                    'Authorization' => $authHeader
                )
            )
        );
    }
}