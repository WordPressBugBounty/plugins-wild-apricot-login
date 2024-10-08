<?php

class WA_Installer
{
    public static function install($config)
    {
        if (!current_user_can('activate_plugins'))
        {
            return WA_Error_Handler::handleError('wa_installation_error', __('You are not authorized to activate plugins.', WaIntegrationPlugin::TEXT_DOMAIN));
        }

        WA_Modules_Base_Crypt_AES::install();

        add_option($config['option_name']);
        add_option($config['settings_option_name']);

        $defaultRoleId = get_option('default_role');
        $defaultRole = get_role($defaultRoleId);
        $capabilities = ($defaultRole instanceof WP_Role && is_array($defaultRole->capabilities)) ? $defaultRole->capabilities : array();

        add_role($config['wa_default_role'], __('WA non-member contacts', WaIntegrationPlugin::TEXT_DOMAIN), $capabilities);

        return true;
    }

    public static function uninstall($config)
    {
        if (!current_user_can('activate_plugins'))
        {
            return WA_Error_Handler::handleError('wa_installation_error', __('You are not authorized to deactivate plugins.', WaIntegrationPlugin::TEXT_DOMAIN));
        }

        delete_metadata('user', 0, $config['logged_in_session_name'], '', true);
        delete_metadata('user', 0, WA_Modules_Base_WaContact_MetaData::ID_META_NAME, '', true);
        delete_metadata('user', 0, WA_Modules_Base_WaContact_MetaData::OPTION_NAME, '', true);
        delete_option($config['option_name']);
        delete_option($config['settings_option_name']);
    }

    public static function preUpgrade()
    {
        WA_Modules_Base_Crypt_AES::preUpgrade();
    }

    public static function postUpgrade()
    {
        WA_Modules_Base_Crypt_AES::postUpgrade();
    }
}