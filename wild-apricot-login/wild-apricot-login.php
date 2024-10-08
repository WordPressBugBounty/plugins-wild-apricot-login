<?php

/*
    Plugin Name: Wild Apricot Login
    Plugin URI: http://www.wildapricot.com/
    Description: Provides single sign-on service for Wild Apricot members to provide access to restricted Wild Apricot content.
    Version: 1.0.16
    Author: Wild Apricot
    Author URI: http://www.wildapricot.com/
    License: GPL2
*/

$waIntegrationPlugin = new WaIntegrationPlugin();

class WaIntegrationPlugin
{
    const TEXT_DOMAIN = 'wa-integration';

    private static $config = array
    (
        // General settings
        'version' => '1.0.16',

        // WA services settings
        'wa_oauth_provider_url' => 'https://oauth.wildapricot.org', // wa OAuth2 service url
        'wa_api_url' => 'https://api.wildapricot.org/v2', // wa API service url

        'images_dir' => 'img',
        'js_dir' => 'js',
        'css_dir' => 'css',

        // Class name settings
        'class_name_separator' => '_',
        'class_name_prefix' => 'WA',

        // Path settings
        'path_separator' => '/',
        'php_file_extension' => '.php',

        // Options settings
        'option_name' => 'wa_integration_options',
        'settings_option_name' => 'wa_integration_settings',

        // Session settings
        'session_name' => 'wa_session',
        'session_expiration_time' => 1800,
        'session_expiration_offset' => 300,
        'logged_in_session_name' => 'wa_logged_in_session',

        'wa_role_prefix' => 'wa_level_',
        'wa_default_role' => 'wa_level_0',
        'wa_contact_login_prefix' => 'wa_contact_',

        // Modules settings
        'modules_path' => 'Modules',
        'module_class_name' => 'Module',
        'core_module_data' => array('id' => 'Core', 'name' => 'Core module', 'isActive' => true),
        'modules_data' => array
        (
            array('id' => 'Authorization', 'name' => 'Authorization module', 'isActive' => true),
            array('id' => 'Synchronization', 'name' => 'Synchronization module', 'isActive' => true),
            array('id' => 'ContentRestriction', 'name' => 'Content restriction module', 'isActive' => true),
            array('id' => 'Settings', 'name' => 'Settings module', 'isActive' => true)
        )
    );

    private static $classLoader;

    public function __construct()
    {
        self::initClassLoader();

        add_action('plugins_loaded', array($this, 'initModules'));

        register_activation_hook(__FILE__, array(__CLASS__, 'install'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        add_filter('upgrader_pre_install', array($this, 'preUpgrade'), 10, 2);
        add_filter('upgrader_post_install', array($this, 'postUpgrade'), 10, 2);
    }

    public function preUpgrade($res, $args)
    {
        if (isset($args['plugin']) && $args['plugin'] == 'wild-apricot-login/wild-apricot-login.php')
        {
            WA_Installer::preUpgrade();
        }
    }

    public function postUpgrade($res, $args)
    {
        if (isset($args['plugin']) && $args['plugin'] == 'wild-apricot-login/wild-apricot-login.php')
        {
            WA_Installer::postUpgrade();
        }
    }

    public static function install()
    {
        $result = WA_Installer::install(self::$config);

        if (is_wp_error($result))
        {
            trigger_error($result->get_error_message(), E_USER_ERROR);
        }
    }

    public static function uninstall()
    {
        WA_Installer::uninstall(self::$config);
    }

    public function getConfigOption($configKey)
    {
        return isset(self::$config[$configKey]) ? self::$config[$configKey] : null;
    }

    public function initModules()
    {
        $core = $this->initCoreModule();

        foreach (self::$config['modules_data'] as $moduleData)
        {
            $module = $this->initModule($moduleData);

            if ($moduleData['isActive'])
            {
                $module->activate($core);
            }
        }
    }

    private function initCoreModule()
    {
        $core = $this->initModule(self::$config['core_module_data']);
        $core->activate($core);

        return $core;
    }

    private function initModule($moduleData)
    {
        $moduleClass = implode(
            self::$config['class_name_separator'],
            array(self::$config['class_name_prefix'], self::$config['modules_path'], $moduleData['id'], self::$config['module_class_name'])
        );

        $module = new $moduleClass($moduleData['id'], $moduleData['name'], $this);

        return $module;
    }

    private static function initClassLoader()
    {
        if (empty(self::$classLoader))
        {
            $pluginDir = wp_normalize_path(dirname(__FILE__) . self::$config['path_separator']);

            require_once($pluginDir . 'ClassLoader' . self::$config['php_file_extension']);

            self::$classLoader = new WA_ClassLoader(
                $pluginDir,
                self::$config['class_name_prefix'],
                self::$config['class_name_separator'],
                self::$config['path_separator'],
                self::$config['php_file_extension']
            );
        }
    }
}