<?php

class WA_Modules_Authorization_WaLogin_Widget extends WP_Widget
{
    const WIDGET_ID = 'wa_login_widget';
    const RENDER_HOOK = 'wa_login_widget_render';
    const SET_DEFAULTS_HOOK = 'wa_login_widget_set_defaults';
    const GET_LOGIN_LABEL_HOOK = 'wa_login_widget_get_login_label';

    const LOGIN_LABEL_ID = WA_Modules_Authorization_WaLogin_ShortCode::LOGIN_LABEL_ID;
    const REDIRECT_PAGE_ID = WA_Modules_Authorization_WaLogin_ShortCode::REDIRECT_PAGE_ID;

    private $fields = array(self::LOGIN_LABEL_ID, self::REDIRECT_PAGE_ID);
    private $formFieldTitles;
    private $formFieldDesc;
    private $defaultValues;

    public function __construct()
    {
        $widgetOptions = array
        (
            'description' => __('Provides single sign-on service for Wild Apricot members, allowing them to access restricted Wild Apricot content from your WordPress site.', WaIntegrationPlugin::TEXT_DOMAIN)
        );

        $controlOptions = array
        (
            'id_base' => self::WIDGET_ID
        );

        parent::__construct(self::WIDGET_ID, __('Wild Apricot Login', WaIntegrationPlugin::TEXT_DOMAIN), $widgetOptions, $controlOptions);

        $this->formFieldTitles = array_combine
        (
            $this->fields,
            array(
                __('Login button label:', WaIntegrationPlugin::TEXT_DOMAIN),
                __('Redirect page:', WaIntegrationPlugin::TEXT_DOMAIN)
            )
        );
        $this->formFieldDesc = array_combine
        (
            $this->fields,
            array('', __('Redirect members to this page after log in.<br /> Leave empty for current page.', WaIntegrationPlugin::TEXT_DOMAIN))
        );
        $this->defaultValues = apply_filters(self::SET_DEFAULTS_HOOK, array_combine($this->fields, array('', '')));
    }

    public function widget($args, $instance)
    {
        $loginLabel = apply_filters('widget_text', $instance[self::LOGIN_LABEL_ID], $instance);
        $redirectPage = empty($instance[self::REDIRECT_PAGE_ID]) ? '' : $instance[self::REDIRECT_PAGE_ID];

        $labels = array_combine($this->fields, array($loginLabel, $redirectPage));

        echo $args['before_widget'];
        echo apply_filters(self::RENDER_HOOK, $labels);
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $this->handleWidgetArgs($this->defaultValues, $instance, null, array($this, 'renderFormField'));
    }

    public function update($new, $old)
    {
        return $this->handleWidgetArgs($this->defaultValues, $new, $old, array($this, 'sanitizeFormValue'));
    }

    public function renderFormField($key, $value)
    {
        $fieldId = $this->get_field_id($key);
        $fieldDesc = !empty($this->formFieldDesc[$key]) ? '<div class="description">' . $this->formFieldDesc[$key] . '</div>' : '';

        echo '<p><label for="' . $fieldId . '">' . $this->formFieldTitles[$key] . '</label>';
        echo '<input class="widefat" id="' . $fieldId . '" name="' . $this->get_field_name($key) . '" type="text" value="' . esc_attr($value) . '">';
        echo $fieldDesc . '</p>';
    }

    public function sanitizeFormValue($key, $value)
    {
        $value = WA_Utils::sanitizeString($value);

        switch($key)
        {
            case self::REDIRECT_PAGE_ID:
            {
                $value = WA_Utils::isNotEmptyString($value) ? '/' . ltrim(wp_make_link_relative($value), '/') : '';
                break;
            }

            case self::LOGIN_LABEL_ID:
            {
                $value = apply_filters(self::GET_LOGIN_LABEL_HOOK, $value);
                break;
            }
        }

        return $value;
    }

    private function handleWidgetArgs(array $params, $args, $instance = null, $callback = null)
    {
        $result = array();
        $isCallbackCallable = is_callable($callback);

        foreach ($params as $key => $defaultValue)
        {
            if ($isCallbackCallable)
            {
                $result[$key] = call_user_func($callback, $key, $this->getWidgetArgValue($key, $args, $instance, $defaultValue));
            }
            else
            {
                $result[$key] = $this->getWidgetArgValue($key, $args, $instance, $defaultValue);
            }
        }

        return $result;
    }

    private function getWidgetArgValue($key, $args, $instance = null, $defaultValue = '')
    {
        if (is_array($args) && isset($args[$key]))
        {
            return $args[$key];
        }

        if (is_array($instance) && isset($instance[$key]))
        {
            return $instance[$key];
        }

        return $defaultValue;
    }
} 