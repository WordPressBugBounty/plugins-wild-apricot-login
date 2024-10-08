<?php

class WA_Modules_Synchronization_UserProfile
{
    private $module;
    private $extraColumns;

    public function __construct(WA_Modules_Interfaces_IModule $module, array $extraColumns = array())
    {
        $this->module = $module;
        $this->extraColumns = $extraColumns;

        add_action('show_user_profile', array($this, 'showWaFields'));
        add_action('edit_user_profile', array($this, 'showWaFields'));
        /*add_filter('manage_users_columns', array($this, 'addUserListExtras'));
        add_action('manage_users_custom_column', array($this, 'renderUserListExtras'), 10, 3);*/
    }

    public function showWaFields($wpUser)
    {
        $waContactMetaData = new WA_Modules_Base_WaContact_MetaData(0, $wpUser);

        echo '<h3>' . __('Extra information', WaIntegrationPlugin::TEXT_DOMAIN) . '</h3>';
        echo '<table class="form-table"><tbody>';

        foreach ($this->extraColumns as $metaKey => $title)
        {
            $value = $waContactMetaData->$metaKey;

            echo '<tr><th>' . esc_html($title) . '</th><td>' . (empty($value) ? __('not specified') : esc_html($value)) . '</td></tr>';
        }

        echo '</tbody></table>';
    }

    /*public function addUserListExtras($columns)
    {
        return array_merge($columns, $this->extraColumns);
    }

    public function renderUserListExtras($value, $columnName, $userId)
    {
        if (in_array($columnName, array_keys($this->extraColumns)))
        {
            $value = get_user_meta($userId, $columnName, true);

            return empty($value) ? 'not specified' : esc_html($value);
        }

        return $value;
    }*/
}