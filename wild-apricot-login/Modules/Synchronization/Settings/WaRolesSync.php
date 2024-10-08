<?php

class WA_Modules_Synchronization_Settings_WaRolesSync extends WA_Modules_Base_Setting_Button
{
    const BUTTON_ACTION_HOOK = 'wa_integration_sync_wa_roles_button_action';

    public function onSettingsRegister(WA_Modules_Interfaces_ISettingsManager $settingsManager)
    {
        parent::onSettingsRegister($settingsManager);

        add_action('wp_ajax_' . self::BUTTON_ACTION_HOOK, array($this, 'onButtonClick'));
    }

    public function enqueueScript()
    {
        $scriptId = $this->module->enqueueScript('Settings/WaRolesSync.js');

        wp_localize_script($scriptId, 'WaRolesSyncSettingData', array(
            'buttonId' => $this->getFieldId(),
            'descriptionId' => $this->getDescriptionId(),
            'spinnerId' => $this->getSpinnerId(),
            'url' => $this->args['actionUrl'],
            'action' => self::BUTTON_ACTION_HOOK,
            'updateRolesErrorMessage' => $this->args['updateRolesErrorMessage'],
            'waitMessage' => $this->args['waitMessage']
        ));
    }

    public function renderSection()
    {
        echo '&nbsp; <hr />';
    }

    public function onButtonClick()
    {
        if (!current_user_can('promote_users'))
        {
            $result = WA_Error_Handler::handleError('wa_integration_sync_error', 'You are not authorized to update user roles.');
        }
        else
        {
            $result = $this->module->syncWaRoles($this->module->getWaLevels());
        }

        $message = $this->args['successMessage'];

        if (is_wp_error($result))
        {
            $message = $result->get_error_message();
        }

        die(json_encode(array('message' => $message)));
    }
}