<?php

class WA_Modules_Synchronization_Module extends WA_Modules_Base_Module implements WA_Modules_Interfaces_ISynchronizer
{
    const SYNC_WA_ROLES_BUTTON_ID = 'waRolesSyncButton';
    const API_GET_LEVELS_PATH = '/MembershipLevels';

    private $rolesSynchronizer;
    private $userSynchronizer;
    private $updateRolesErrorMessage;

    public function activate(WA_Modules_Interfaces_ICore $core, array $args = null)
    {
        parent::activate($core, $args);

        $this->updateRolesErrorMessage = __('Unable to update roles.', WaIntegrationPlugin::TEXT_DOMAIN);
        $this->rolesSynchronizer = new WA_Modules_Synchronization_RolesSynchronizer($this);
        $this->userSynchronizer = new WA_Modules_Synchronization_UserSynchronizer($this);

        $levelsSyncSetting = new WA_Modules_Synchronization_Settings_WaRolesSync
        (
            $this,
            self::SYNC_WA_ROLES_BUTTON_ID,
            __('Update roles with Wild Apricot membership levels', WaIntegrationPlugin::TEXT_DOMAIN),
            array
            (
                'buttonTitle' => __('Update', WaIntegrationPlugin::TEXT_DOMAIN),
                'description' => __('During update, membership levels will be added as roles if they do not already exist,<br /> or updated if they do. No roles will be deleted.', WaIntegrationPlugin::TEXT_DOMAIN),
                'actionUrl' => admin_url('admin-ajax.php'),
                'successMessage' => __('Update complete.', WaIntegrationPlugin::TEXT_DOMAIN),
                'updateRolesErrorMessage' => $this->updateRolesErrorMessage,
                'waitMessage' => __('Please wait while roles are updated.', WaIntegrationPlugin::TEXT_DOMAIN)
            )
        );

        $userProfileSync = new WA_Modules_Synchronization_UserProfile(
            $this,
            array(
                'organization' => __('Organization', WaIntegrationPlugin::TEXT_DOMAIN),
                'membershipStatus' => __('Membership status', WaIntegrationPlugin::TEXT_DOMAIN)
            )
        );

        add_action(WA_Modules_Interfaces_ICore::WA_CONTACT_DATA_LOADED, array($this, 'syncWaContact'));
    }

    public function syncWaContact($waContact)
    {
        $this->userSynchronizer->syncWaContact($waContact);
    }

    public function getWpUserByWaContact($waContact)
    {
        return $this->core->getWpUserByWaContact($waContact);
    }

    public function syncWaRoles($waLevels)
    {
        return $this->rolesSynchronizer->syncWaRoles($waLevels);
    }

    public function getWaLevels()
    {
        return $this->core->sendWaApiRequest(self::API_GET_LEVELS_PATH);
    }

    public function getWaDefaultRole()
    {
        return $this->core->getWaDefaultRole();
    }

    public function getWaRoleId($waLevelId)
    {
        return $this->core->getWaRoleId($waLevelId);
    }

    public function isWaLevelValid($waLevel)
    {
        return is_object($waLevel) && !empty($waLevel->Id) && !empty($waLevel->Name);
    }

    public function getWaUserLogin($waContactId)
    {
        return $this->core->getWaUserLogin($waContactId);
    }

    public function isSettingsValid()
    {
        return $this->core->isSettingsValid();
    }

    public function getUpdateRolesErrorMessage()
    {
        return $this->updateRolesErrorMessage;
    }
} 