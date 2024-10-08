<?php

class WA_Modules_Authorization_Module extends WA_Modules_Base_Module implements WA_Modules_Interfaces_IAuthorization
{
    const OAUTH2_CLIENT_ID = 'OAuth2ClientId';
    const OAUTH2_SECRET_ID = 'OAuth2Secret';
    const OAUTH2_IS_VALID_ID = 'OAuth2IsValid';

    const LOGIN_FORM_SECTION_ID_POSTFIX = '_LoginForm';
    const LOGIN_BUTTON_LABEL_ID = 'loginFormLabel';

    private $loginController;

    public function activate(WA_Modules_Interfaces_ICore $core, array $args = null)
    {
        parent::activate($core, $args);

        $this->loginController = new WA_Modules_Authorization_WaLogin_Controller($this, $this->plugin->getConfigOption('logged_in_session_name'), $core->getWaAccount());

        $oAuth2ClientIdSetting = new WA_Modules_Authorization_Settings_ClientId($this, self::OAUTH2_CLIENT_ID, __('Client ID', WaIntegrationPlugin::TEXT_DOMAIN));
        $oAuth2SecretSetting = new WA_Modules_Authorization_Settings_ClientSecret($this, self::OAUTH2_SECRET_ID, __('Client secret', WaIntegrationPlugin::TEXT_DOMAIN));
        $loginFormButtonLabelSetting = new WA_Modules_Authorization_Settings_LoginButtonLabel(
            $this,
            self::LOGIN_BUTTON_LABEL_ID,
            __('Default login button label', WaIntegrationPlugin::TEXT_DOMAIN)
        );

        add_filter(WA_Modules_Interfaces_ICore::GET_WA_LOGIN_FORM, array($this, 'doShortCode'));
    }

    public function doShortCode($args)
    {
        return $this->loginController->doShortCode($args);
    }

    public function getOAuth2Token($code, $redirectUri)
    {
        if (!$this->isSettingsValid())
        {
            return WA_Error_Handler::handleError('wa_integration_oauth2_error', 'WA OAuth2 credentials is empty or invalid.');
        }

        return $this->core->getOAuth2Token($code, $this->getOAuth2ClientId(), $this->getOAuth2Secret(), $redirectUri);
    }

    public function getWpUserByWaOAuth2Token($oAuth2Token)
    {
        return $this->core->getWpUserByWaOAuth2Token($oAuth2Token);
    }

    public function getOAuth2ClientId()
    {
        return $this->getOption(self::OAUTH2_CLIENT_ID);
    }

    public function getOAuth2Secret()
    {
        return $this->getOption(self::OAUTH2_SECRET_ID);
    }

    public function actualizeOAuth2Token($oAuthToken, $clientId, $secret)
    {
        return $this->core->actualizeOAuth2Token($oAuthToken, $clientId, $secret);
    }

    public function getDefaultLoginLabel()
    {
        $loginLabel = $this->getOption(self::LOGIN_BUTTON_LABEL_ID);

        return empty($loginLabel) ? __('Login', WaIntegrationPlugin::TEXT_DOMAIN) : $loginLabel;
    }

    public function getLoginLabel($loginLabel)
    {
        return empty($loginLabel) ? $this->getDefaultLoginLabel() : $loginLabel;
    }

    public function isSettingsValid()
    {
        return $this->getOption(self::OAUTH2_IS_VALID_ID);
    }

    public function updateOAuth2Settings()
    {
        $clientId = $this->getOAuth2ClientId();
        $secret = $this->getOAuth2Secret();

        $this->setOption(
            self::OAUTH2_IS_VALID_ID,
            WA_Utils::isNotEmptyString($clientId) && WA_Utils::isNotEmptyString($secret) && $this->core->isOAuth2SettingsValid($clientId, $secret)
        );
    }
}