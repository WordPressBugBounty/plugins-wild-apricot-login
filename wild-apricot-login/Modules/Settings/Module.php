<?php

class WA_Modules_Settings_Module extends WA_Modules_Base_Module implements WA_Modules_Interfaces_ISettingsManager
{
    const SETTINGS_PAGE = 'wa_integration_settings_page';
    const OPTION_GROUP = 'wa_integration_settings_group';

    const LOCK_ID = 'wa_integration_settings_lock';
    const LOCK_TIME = 15;

    private $pageHook;
    private $optionName;
	private $isModuleSettingsValid = array();

    public function activate(WA_Modules_Interfaces_ICore $core, array $args = null)
    {
        parent::activate($core, $args);

        $this->optionName = $this->plugin->getConfigOption('settings_option_name');

        if (is_admin() && current_user_can('manage_options'))
        {
            add_action('admin_menu', array($this, 'createSettingsPage'));
            add_action('admin_init', array($this, 'registerSettings'));
            add_action('admin_enqueue_scripts', array($this, 'notifyEnqueueScript'));
        }
    }

    public function getPageId()
    {
        return self::SETTINGS_PAGE;
    }

    public function getOptionName()
    {
        return $this->optionName;
    }

    public function createSettingsPage()
    {
        if (!current_user_can('manage_options')) { return; }

        $this->pageHook = add_options_page
        (
            __('Wild Apricot Login Settings', WaIntegrationPlugin::TEXT_DOMAIN),
            __('Wild Apricot Login', WaIntegrationPlugin::TEXT_DOMAIN),
            'manage_options',
            self::SETTINGS_PAGE,
            array($this, 'buildSettingsPage')
        );
    }

    public function buildSettingsPage()
    {
        if (!current_user_can('manage_options')) { return; }

        echo '<div class="wrap"><h2>' . __('Wild Apricot Login Settings', WaIntegrationPlugin::TEXT_DOMAIN) . '</h2>';
        echo '<p>';
        echo __('This plugin integrates Wild Apricot authentication into your WordPress site, allowing Wild Apricot members to access restricted content.', WaIntegrationPlugin::TEXT_DOMAIN);
        echo '</p>';
        echo '<form method="post" action="options.php" class="wa_integration_settings">';

        settings_fields(self::OPTION_GROUP);
        $this->notifySettingsPageRender();
        do_settings_sections(self::SETTINGS_PAGE);

        submit_button(__('Save changes', WaIntegrationPlugin::TEXT_DOMAIN));
        echo '</form></div>';
    }

    public function notifySettingsPageRender()
    {
        do_action(self::SETTINGS_RENDER_HOOK, $this);
    }

    public function notifyEnqueueScript($hook)
    {
        if ($hook == $this->pageHook)
        {
            $this->enqueueStyle('admin/styles.css');
            $this->enqueueScript('WaHiddenTextField.js');
            do_action(self::SETTINGS_ENQUEUE_SCRIPT_HOOK);
        }
    }

    public function registerSettings()
    {
        if (!current_user_can('manage_options')) { return; }

        register_setting(self::OPTION_GROUP, $this->optionName, array($this, 'notifySettingsUpdate'));
        $this->notifySettingsRegister();
    }

    public function notifySettingsRegister()
    {
        do_action(self::SETTINGS_REGISTER_HOOK, $this);
    }

    public function notifySettingsUpdate(array $settingsToUpdate)
    {
        if (!current_user_can('manage_options')) { return null; }

        WA_Lock_Provider::wait(self::LOCK_ID, self::LOCK_TIME);
        WA_Lock_Provider::acquire(self::LOCK_ID, self::LOCK_TIME);

        do_action(self::SETTINGS_UPDATE_HOOK, $settingsToUpdate, $this);
        $this->core->saveOptions();

        WA_Lock_Provider::release(self::LOCK_ID);
		
		if (in_array(false, $this->isModuleSettingsValid))
		{
			add_settings_error('general', 'settings_updated', __('Settings saved. Some errors occured.'), 'warning');
		}

        return array();
    }
	
	public function onSettingsUpdate($moduleId, $isValid)
	{
		$this->isModuleSettingsValid[$moduleId] = (bool)$isValid;
	}
} 