<?php

class WA_Modules_Authorization_WaLogin_Controller
{
    const SHORT_CODE_NAME = 'wa_login';

    const OAUTH2_LOGIN_URL_TEMPLATE = 'https://%s/widget/sys/login/OAuthLogin';
    const OAUTH2_LOGOUT_NONCE_URL_TEMPLATE = 'https://%s/widget/sys/login/logoutnonce';
    const OAUTH2_LOGOUT_URL_TEMPLATE = 'https://%s/widget/sys/login/logout?nonce=%s';
    const OAUTH2_SCOPE = 'contacts_me';
    const OAUTH2_RESPONSE_TYPE = 'authorization_code';

    const LOGIN_STATE = 'WaWpIntegrationLogin';
    const LOGIN_REDIRECT_URI_ID = 'LoginRedirectUri';
    const LOGOUT_STATE = 'WaWpIntegrationLogout';
    const LOGIN_ERROR_ID = 'LoginError';
    const LOGIN_ACTION_ID = 'waLoginAction';
    const CODE_ID = 'code';
    const STATE_ID = 'state';
    CONST WA_SESSION_KEY = 'sk';

    private $module;
    private $shortCode;
    private $optionName;
    private $oAuth2Token;
    private $wpUserId;
    private $valid;
    private $errorMessage;
    private $defaultErrorMessage;
    private $selfLogout = false;
    private $logoutNonce;
    private $waApiAccount;
    private $waSessionKey;

    public function __construct(WA_Modules_Authorization_Module $module, $optionName, WA_Modules_Core_WaApi_Account $waApiAccount = null)
    {
        $this->module = $module;
        $this->optionName = $optionName;
        $this->waApiAccount = $waApiAccount;
        $this->defaultErrorMessage = __('An unknown error has occurred. Please try again later.', WaIntegrationPlugin::TEXT_DOMAIN);

        if (!$this->module->isSettingsValid() || empty($waApiAccount) || !$waApiAccount->isValid())
        {
            $this->valid = false;
        }
        else
        {
            $this->valid = true;

            add_action('init', array($this, 'onWpInit'));
            add_action('clear_auth_cookie', array($this, 'onWpClearAuthCookie'));
            add_action('wp_logout', array($this, 'onWpLogout'));
            add_action('login_form_logout', array($this, 'onWpFormLogout'));
            add_action('wp_enqueue_scripts', array($this, 'addWaWidgetJsHandler'));
        }

        $this->shortCode = new WA_Modules_Authorization_WaLogin_ShortCode(
            $module,
            self::SHORT_CODE_NAME,
            array(
                'controller' => $this,
                'actionId' => self::LOGIN_ACTION_ID,
                'loginUrl' => $this->valid ? sprintf(self::OAUTH2_LOGIN_URL_TEMPLATE, $this->waApiAccount->getPrimaryDomainName()) : ''
            )
        );

        add_action('widgets_init', array($this, 'registerWidget'));
    }

    public function addWaWidgetJsHandler()
    {
        $scriptId = $this->module->enqueueScript('WaWidgetHandler.js');
        $isLoggedIn = is_user_logged_in();

        if ($isLoggedIn)
        {
            $wpUser = wp_get_current_user();
            $oAuthToken = $this->getWaToken($wpUser);
            $isLoggedIn = !is_null($oAuthToken);
        }

        $getParams = $this->mixInGetParams(
            array(
                self::STATE_ID => self::LOGOUT_STATE,
                self::LOGIN_ACTION_ID => 'Logout'
            )
        );

        $arrRedirectUri = explode('?', $this->getRedirectUri(), 2);

        wp_localize_script($scriptId, 'WaWidgetHandlerData', array(
            'isLoggedIn' => $isLoggedIn,
            'logoutUrl' => $arrRedirectUri[0] . '?' . http_build_query($getParams),
            'loginForm' => $this->shortCode->render(array(WA_Modules_Authorization_WaLogin_ShortCode::REDIRECT_PAGE_ID => ''), '', self::SHORT_CODE_NAME),
            'sessionKey' => $this->waSessionKey
        ));
    }

    public function onWpInit()
    {
        $state = isset($_GET[self::STATE_ID]) ? WA_Utils::sanitizeString($_GET[self::STATE_ID]) : '';

        $this->resetLoginData();

        switch ($state)
        {
            case self::LOGOUT_STATE:
            {
                $this->selfLogout = true;
                wp_logout();
                break;
            }

            case self::LOGIN_STATE:
            {
                $code = WA_Utils::sanitizeString($_GET[self::CODE_ID]);

                if (empty($code)) { return; }

                $loginResult = $this->login($code);

                if (is_wp_error($loginResult))
                {
                    $this->storeErrorMessage($loginResult, false);
                }

                break;
            }
        }
    }

    public function onWpClearAuthCookie()
    {
        if (is_super_admin()) { return; }

        $wpUser = wp_get_current_user();
        $oAuthToken = $this->getWaToken($wpUser);

        if (is_null($oAuthToken)) { return; }

        $oAuthToken = $this->module->actualizeOAuth2Token($oAuthToken, $this->module->getOAuth2ClientId(), $this->module->getOAuth2Secret());

        if (is_wp_error($oAuthToken))
        {
            $this->storeErrorMessage($oAuthToken, true);
            return;
        }

        $nonce = $this->getLogoutNonce($oAuthToken->getAccessToken(), $wpUser->user_email, $this->getRedirectUri());

        if (is_wp_error($nonce))
        {
            $this->storeErrorMessage($nonce, true);
            return;
        }

        $this->logoutNonce = $nonce;
    }

    public function onWpLogout()
    {
        if (!empty($this->logoutNonce))
        {
            wp_redirect($this->getWaLogoutUrl($this->logoutNonce));
            exit;
        }

        if ($this->selfLogout)
        {
            wp_safe_redirect($this->getRedirectUri());
            exit;
        }
    }

    public function onWpFormLogout()
    {
        if (!is_user_logged_in())
        {
            wp_safe_redirect(wp_get_referer());
            exit;
        }
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function registerWidget()
    {
        add_filter(WA_Modules_Authorization_WaLogin_Widget::SET_DEFAULTS_HOOK, array($this, 'setLoginWidgetDefaults'));
        add_filter(WA_Modules_Authorization_WaLogin_Widget::GET_LOGIN_LABEL_HOOK, array($this->module, 'getLoginLabel'));
        add_filter(WA_Modules_Authorization_WaLogin_Widget::RENDER_HOOK, array($this, 'doWidgetShortCode'));
        register_widget('WA_Modules_Authorization_WaLogin_Widget');
    }

    public function setLoginWidgetDefaults($defaults)
    {
        $shortCodeDefaults = $this->getShortCodeDefaults();
        $defaults[WA_Modules_Authorization_WaLogin_ShortCode::LOGIN_LABEL_ID] = $shortCodeDefaults[WA_Modules_Authorization_WaLogin_ShortCode::LOGIN_LABEL_ID];

        return $defaults;
    }

    public function doWidgetShortCode(array $args = array())
    {
        return $this->doShortCode($args);
    }

    public function doShortCode($args)
    {
        if (!shortcode_exists(self::SHORT_CODE_NAME))
        {
            WA_Error_Handler::handleError('wa_integration_shortcode_is_not_exists', 'Shortcode "' . self::SHORT_CODE_NAME . '" is not exists."');
            return '';
        }

        return do_shortcode($this->shortCode->getShortCodeString($args));
    }

    public function getLoginArgs($redirectUri = '')
    {
        return array
        (
            'scope' => self::OAUTH2_SCOPE,
            'client_id' => $this->module->getOAuth2ClientId(),
            'response_type' => self::OAUTH2_RESPONSE_TYPE,
            'claimed_account_id' => $this->waApiAccount->getId(),
            'state' => self::LOGIN_STATE,
            'redirect_uri' => $this->getRedirectUri($redirectUri)
        );
    }

    public function getLogoutArgs()
    {
        return $this->mixInGetParams(
            array(
                self::STATE_ID => self::LOGOUT_STATE
            )
        );
    }

    public function getRedirectUri($redirectUri = '', $removeLoginArgs = true)
    {
        $homeUrl = home_url();
        $homePath = wp_make_link_relative($homeUrl);

        $homeUrl = ($homeUrl == $homePath) ? $homeUrl : substr($homeUrl, 0, strlen($homeUrl) - strlen($homePath));

        $redirectUri = WA_Utils::isNotEmptyString($redirectUri)
            ? WA_Utils::sanitizeString($redirectUri)
            : WA_Utils::sanitizeString($_SERVER['REQUEST_URI']);

        $redirectUri = wp_make_link_relative($redirectUri);
        $redirectUri = rtrim($homeUrl, '/') . '/' . ltrim($redirectUri, '/');

        if ($removeLoginArgs)
        {
            $redirectUri = remove_query_arg(array(self::CODE_ID, self::STATE_ID, self::LOGIN_ACTION_ID, self::WA_SESSION_KEY), $redirectUri);
        }

        return esc_url_raw($redirectUri);
    }

    public function completeLogin($cookie)
    {
        $components = array(
            'oAuthToken' => $this->oAuth2Token,
            'sessionKey' => $this->waSessionKey
        );

        $repository = new WA_Modules_Authorization_WaLogin_Sessions_Repository($this->wpUserId, $this->optionName);
        $repository->setAuthComponents($components, $cookie);
    }

    public function getShortCodeDefaults()
    {
        return array(
            WA_Modules_Authorization_WaLogin_ShortCode::LOGIN_LABEL_ID => $this->module->getDefaultLoginLabel(),
            WA_Modules_Authorization_WaLogin_ShortCode::LOGOUT_LABEL_ID => __('Logout', WaIntegrationPlugin::TEXT_DOMAIN),
            WA_Modules_Authorization_WaLogin_ShortCode::REDIRECT_PAGE_ID => ''
        );
    }

    private function getWaToken($wpUser)
    {
        $repository = new WA_Modules_Authorization_WaLogin_Sessions_Repository($wpUser->ID, $this->optionName);
        $components = $repository->getAuthComponents();

        $oAuthToken = $components['oAuthToken'];
        $sessionKey = $components['sessionKey'];

        if (isset($sessionKey))
        {
            $this->waSessionKey = $sessionKey;
        }

        if (is_object($oAuthToken) && $oAuthToken instanceof WA_Modules_Core_WaOAuth_Token && $oAuthToken->isValid())
        {
            return $oAuthToken;
        }

        return null;
    }

    private function login($code)
    {
        $redirectUri = $this->getRedirectUri();
        $this->oAuth2Token = $this->module->getOAuth2Token($code, $redirectUri);

        if (is_wp_error($this->oAuth2Token))
        {
            return $this->oAuth2Token;
        }

        $user = $this->module->getWpUserByWaOAuth2Token($this->oAuth2Token);

        if (is_wp_error($user))
        {
            return $user;
        }

        if ($user instanceof WP_User)
        {
            if (is_super_admin($user->ID))
            {
                return WA_Error_Handler::handleError('wa_integration_login_error', 'Admin login is disabled.');
            }

            $this->wpUserId = $user->ID;

            if (isset($_GET[self::WA_SESSION_KEY]))
            {
                $this->waSessionKey = WA_Utils::sanitizeString($_GET[self::WA_SESSION_KEY]);
            }

            add_action('set_logged_in_cookie', array($this, 'completeLogin'));
            wp_set_auth_cookie($user->ID);
            wp_safe_redirect($redirectUri);
            exit;
        }

        return WA_Error_Handler::handleError('wa_integration_login_error', 'Unable to get WordPress user.');
    }

    private function storeErrorMessage($wpError, $storeInSession = false)
    {
        if (is_wp_error($wpError))
        {
            $this->errorMessage = $this->defaultErrorMessage;

            if ($storeInSession)
            {
                $this->module->setSessionData(self::LOGIN_ERROR_ID, $this->errorMessage);
            }
        }
    }

    private function resetLoginData()
    {
        $storedError = $this->module->getSessionData(self::LOGIN_ERROR_ID);

        if (WA_Utils::isNotEmptyString($storedError))
        {
            $this->errorMessage = $storedError;
        }

        $this->module->unsetSessionData(self::LOGIN_ERROR_ID);
        $this->oAuth2Token = null;
        $this->wpUserId = null;
    }

    private function getLogoutNonce($accessToken, $email, $redirectUri)
    {
        $httpClient = new WA_Http_Client();
        $response = $httpClient->sendRequest(
            sprintf(self::OAUTH2_LOGOUT_NONCE_URL_TEMPLATE, $this->waApiAccount->getPrimaryDomainName()),
            array(
                'method' => 'POST',
                'body' => array(
                    'token' => $accessToken,
                    'email' => $email,
                    'redirectUrl' => $redirectUri
                )
            )
        );

        if (is_wp_error($response) || !WA_Utils::isNotEmptyArray($response) || empty($response['body']))
        {
            return WA_Error_Handler::handleError('wa_integration_logout_error', 'Unable to get logout nonce.');
        }

        $result = json_decode($response['body']);

        if (!is_object($result) || empty($result->nonce))
        {
            return WA_Error_Handler::handleError('wa_integration_logout_error', 'Unable to get logout nonce.');
        }

        return WA_Utils::sanitizeString($result->nonce);
    }

    private function getWaLogoutUrl($nonce)
    {
        return sprintf(self::OAUTH2_LOGOUT_URL_TEMPLATE, $this->waApiAccount->getPrimaryDomainName(), $nonce);
    }

    private function mixInGetParams(array $extraParams = array())
    {
        if (!WA_Utils::isNotEmptyArray($_GET)) { return $extraParams; }

        $getKeys = array_map(array('WA_Utils', 'sanitizeString'), array_keys($_GET));
        $getValues = array_map(array('WA_Utils', 'sanitizeString'), array_values($_GET));
        $getParams = array_combine($getKeys, $getValues);

        return array_merge($getParams, $extraParams);
    }
}