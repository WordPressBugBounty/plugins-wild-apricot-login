<?php

class WA_Modules_Core_Module extends WA_Modules_Base_Module implements WA_Modules_Interfaces_ICore
{
    const API_KEY_ID = 'waApiKey';

    private $options;
    private $session;
    private $oAuthClient;
    private $apiClient;
    private $waDefaultRole;

    public function activate(WA_Modules_Interfaces_ICore $core, array $args = null)
    {
        parent::activate($core, $args);

        $this->options = new WA_Modules_Core_Options($this->plugin->getConfigOption('option_name'));
        $this->session = new WA_Modules_Core_Session_Manager(
            $this->plugin->getConfigOption('session_name'),
            $this->plugin->getConfigOption('session_expiration_time'),
            $this->plugin->getConfigOption('session_expiration_offset')
        );

        $this->oAuthClient = new WA_Modules_Core_WaOAuth_Client($this, $this->plugin->getConfigOption('wa_oauth_provider_url'));
        $this->apiClient = new WA_Modules_Core_WaApi_Client($this, $this->oAuthClient, $this->plugin->getConfigOption('wa_api_url'));

        $apiCredentialsSetting = new WA_Modules_Core_Settings_ApiCredentials($this, self::API_KEY_ID, __('API key', WaIntegrationPlugin::TEXT_DOMAIN));
    }

    public function getWaLoginForm(array $attr = array())
    {
        return apply_filters(self::GET_WA_LOGIN_FORM, $attr);
    }

    public function getModuleOption($moduleId, $optionKey, $forceLoad = false)
    {
        return $this->options->getOption($moduleId, $optionKey, $forceLoad);
    }

    public function setModuleOption($moduleId, $optionKey, $optionValue)
    {
        $this->options->setOption($moduleId, $optionKey, $optionValue);
    }

    public function unsetModuleOption($moduleId, $optionKey)
    {
        $this->options->unsetOption($moduleId, $optionKey);
    }

    public function saveOptions()
    {
        try
        {
            $this->options->save();
        }
        catch (Exception $e)
        {
            WA_Error_Handler::handleError($e->getCode(), $e->getMessage() . "\n Trace: \n" . $e->getTraceAsString());
        }
    }

    public function getModuleSessionData($moduleId, $key)
    {
        return $this->session->get($moduleId, $key);
    }

    public function setModuleSessionData($moduleId, $key, $value)
    {
        $this->session->set($moduleId, $key, $value);
    }

    public function unsetModuleSessionData($moduleId, $key)
    {
        $this->session->remove($moduleId, $key);
    }

    public function getWaAccount()
    {
        return $this->apiClient->getAccount();
    }

    public function sendWaApiRequest($path, $method = 'GET', array $params = null)
    {
        return $this->apiClient->sendApiRequest($path, $method, $params);
    }

    public function getWaDefaultRole()
    {
        if (empty($this->waDefaultRole))
        {
            $this->setWaDefaultRole();
        }

        return $this->waDefaultRole;
    }

    public function isOAuth2SettingsValid($clientId, $secret)
    {
        return $this->oAuthClient->isOAuth2SettingsValid($clientId, $secret);
    }

    public function getOAuth2Token($code, $clientId, $secret, $redirectUri)
    {
        return $this->oAuthClient->getOAuth2Token($code, $clientId, $secret, $redirectUri);
    }

    public function actualizeOAuth2Token($oAuthToken, $clientId, $secret)
    {
        return $this->oAuthClient->actualizeOAuth2Token($oAuthToken, $clientId, $secret);
    }

    public function getWpUserByWaOAuth2Token($oAuth2Token)
    {
        $waContact = $this->apiClient->getWaContactByOAuth2Token($oAuth2Token);

        if (is_wp_error($waContact))
        {
            return $waContact;
        }

        if (!is_object($waContact) || empty($waContact->Id) || empty($waContact->Email) || !is_email($waContact->Email))
        {
            return WA_Error_Handler::handleError('wa_integration_api_error', 'Contact data is invalid');
        }

        do_action(self::WA_CONTACT_DATA_LOADED, $waContact);

        $waContactId = WA_Utils::sanitizeString($waContact->Id);
        $wpUser = $this->getWpUserByWaContactId($waContactId);

        if (!($wpUser instanceof WP_User))
        {
            return WA_Error_Handler::handleError('wa_integration_core_error', 'Unable to get WordPress user by WA contact ID: ' . $waContactId);
        }

        return $wpUser;
    }

    public function getWaUserLogin($waContactId)
    {
        return $this->plugin->getConfigOption('wa_contact_login_prefix') . WA_Utils::sanitizeKey($waContactId);
    }

    public function getWaRoleId($waLevelId)
    {
        return $this->plugin->getConfigOption('wa_role_prefix') . WA_Utils::sanitizeKey($waLevelId);
    }

    public function getWpUserByWaContact($waContact)
    {
        $waContactId = WA_Utils::sanitizeString($waContact->Id);
        $wpUser = $this->getWpUserByWaContactId($waContactId);

        if ($wpUser instanceof WP_User)
        {
            return $wpUser;
        }

        $wpUser = get_user_by('email', WA_Utils::sanitizeEmail($waContact->Email));

        return ($wpUser instanceof WP_User) ? $wpUser : null;
    }

    public function isSettingsValid()
    {
        return $this->oAuthClient->isTokenValid() && $this->apiClient->isAccountValid();
    }

    public function setOAuthToken($apiKey)
    {
        $this->oAuthClient->setToken($apiKey);
    }

    public function setWaAccount()
    {
        $this->apiClient->setAccount();
    }

    private function getWpUserByWaContactId($waContactId)
    {
        $waContactMetaData = new WA_Modules_Base_WaContact_MetaData($waContactId);

        return $waContactMetaData->getWpUser();
    }

    private function setWaDefaultRole()
    {
        $defaultWaRole = get_role($this->plugin->getConfigOption('wa_default_role'));

        if ($defaultWaRole instanceof WP_Role)
        {
            $this->waDefaultRole = $defaultWaRole;
        }
        else
        {
            $defaultRoleId = get_option('default_role');
            $this->waDefaultRole = get_role($defaultRoleId);

            if (!($this->waDefaultRole instanceof WP_Role))
            {
                throw new Exception('Unable to get default WA role');
            }
        }
    }
} 