<?php

class WA_Modules_Authorization_WaLogin_ShortCode extends WA_Modules_Base_ShortCode
{
    const LOGIN_LABEL_ID = 'login_label';
    const LOGOUT_LABEL_ID = 'logout_label';
    const REDIRECT_PAGE_ID = 'redirect_page';

    protected $attributes = array(self::LOGIN_LABEL_ID, self::LOGOUT_LABEL_ID, self::REDIRECT_PAGE_ID);

    public function __construct(WA_Modules_Interfaces_IAuthorization $module, $shortCodeName, array $args = null)
    {
        parent::__construct($module, $shortCodeName, $args);

        $this->formTitle = __('Wild Apricot login button', WaIntegrationPlugin::TEXT_DOMAIN);
        $this->buttonImgUrl = $module->getImageUrl('wa_login_shortcode_editor_button.png');
        $this->defaults = $this->defaults = $this->args['controller']->getShortCodeDefaults();
        $this->formFields = array(
            array(
                'type' => 'textbox',
                'name' => self::LOGIN_LABEL_ID,
                'label' => __('Login button label', WaIntegrationPlugin::TEXT_DOMAIN),
                'tooltip' => __('Leave blank for "' . $this->defaults[self::LOGIN_LABEL_ID] . '"', WaIntegrationPlugin::TEXT_DOMAIN)
            ),
            array(
                'type' => 'textbox',
                'name' => self::LOGOUT_LABEL_ID,
                'label' => __('Logout button label', WaIntegrationPlugin::TEXT_DOMAIN),
                'tooltip' => __('Leave blank for "' . $this->defaults[self::LOGOUT_LABEL_ID] . '"', WaIntegrationPlugin::TEXT_DOMAIN)
            ),
            array(
                'type' => 'textbox',
                'name' => self::REDIRECT_PAGE_ID,
                'label' => __('Redirect page', WaIntegrationPlugin::TEXT_DOMAIN),
                'tooltip' => __('Redirect members to this page after log in. Leave empty for current page.', WaIntegrationPlugin::TEXT_DOMAIN)
            )
        );
    }

    public function render($attributes, $content, $shortCodeName)
    {
        if (!$this->args['controller']->isValid())
        {
            return '<div class="wa_login_shortcode"><p class="error">'
            . __('Please configure "Wild Apricot Login" plugin.')
            . '</p></div>';
        }

        $attr = shortcode_atts($this->defaults, $attributes, $shortCodeName);

        $this->disablePageCache();

        return '<div class="wa_login_shortcode">'
            . (!is_user_logged_in() ? $this->getLoginForm($attr) : $this->getLogoutForm($attr))
            . '</div>';
    }

    public function getShortCodeString($attr)
    {
        $loginLabel = isset($attr[self::LOGIN_LABEL_ID]) ? WA_Utils::sanitizeString($attr[self::LOGIN_LABEL_ID]) : '';
        $logoutLabel = isset($attr[self::LOGOUT_LABEL_ID]) ? WA_Utils::sanitizeString($attr[self::LOGOUT_LABEL_ID]) : '';
        $redirectPage = isset($attr[self::REDIRECT_PAGE_ID]) ? WA_Utils::sanitizeString($attr[self::REDIRECT_PAGE_ID]) : '';

        return '[' . $this->shortCodeName
            . (!empty($loginLabel) ? ' ' . self::LOGIN_LABEL_ID . '="' . esc_attr($loginLabel) . '"' : '')
            . (!empty($logoutLabel) ? ' ' . self::LOGOUT_LABEL_ID . '="' . esc_attr($logoutLabel) . '"' : '')
            . (!empty($redirectPage) ? ' ' . self::REDIRECT_PAGE_ID . '="' . esc_attr($redirectPage) . '"' : '')
            . ']';
    }

    private function getLoginForm($attr)
    {
        $error = $this->args['controller']->getErrorMessage();
        $loginLabel = WA_Utils::sanitizeString($attr[self::LOGIN_LABEL_ID]);
        $loginArgs = $this->args['controller']->getLoginArgs($attr[self::REDIRECT_PAGE_ID]);

        if (empty($loginLabel))
        {
            $loginLabel = $this->defaults[self::LOGIN_LABEL_ID];
        }

        $loginForm = '<form action="' . $this->args['loginUrl'] .'" method="get">';

        foreach ($loginArgs as $key => $value)
        {
            $loginForm .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
        }

        $loginForm .= '<input type="submit" name="' . esc_attr($this->args['actionId']) . '" class="button button-primary" value="' . esc_attr($loginLabel)
            . '" title="' . esc_attr($loginLabel) . '" />';

        if (!empty($error))
        {
            $loginForm .= '<p class="error">' . $error . '</p>';
        }

        return $loginForm . '</form>';
    }

    private function getLogoutForm($attr)
    {
        $currentUser = wp_get_current_user();
        $error = $this->args['controller']->getErrorMessage();
        $logoutLabel = WA_Utils::sanitizeString($attr[self::LOGOUT_LABEL_ID]);
        $logoutArgs = $this->args['controller']->getLogoutArgs();

        if (empty($logoutLabel))
        {
            $logoutLabel = $this->defaults[self::LOGOUT_LABEL_ID];
        }

        $logoutForm = '<form method="get">';
        $logoutForm .= '<p>' . esc_html($currentUser->display_name) . '</p>';

        foreach ($logoutArgs as $key => $value)
        {
            $logoutForm .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
        }

        $logoutForm .= '<input type="submit" name="' . esc_attr($this->args['actionId']) . '" class="button button-primary" value="' . esc_attr($logoutLabel)
            . '" title="' . esc_attr($logoutLabel) . '" />';

        if (!empty($error))
        {
            $logoutForm .= '<p class="error">' . $error . '</p>';
        }

        return $logoutForm . '</form>';
    }
} 