<?php

class WA_Modules_Synchronization_RolesSynchronizer
{
    const UPDATE_ROLES_LOCK_ID = 'wa_integration_update_roles';
    const UPDATE_ROLES_LOCK_TIME = 5;

    const FAKE_WA_ROLE_ID = 'wa_integration_fake_role';

    private $module;
    private $wpRoles;
    private $waLevelsToSync;

    public function __construct(WA_Modules_Synchronization_Module $module)
    {
        $this->module = $module;
        $this->wpRoles = $this->getWpRoles();

        add_filter('pre_update_option_' . $this->wpRoles->role_key, array($this, 'onRolesOptionUpdateStart'), 10, 2);
        add_action('update_option_' . $this->wpRoles->role_key, array($this, 'onRolesOptionUpdateComplete'));

    }

    public function onRolesOptionUpdateStart($newRoles)
    {
        if (isset($newRoles[self::FAKE_WA_ROLE_ID]))
        {
            WA_Lock_Provider::wait(self::UPDATE_ROLES_LOCK_ID, self::UPDATE_ROLES_LOCK_TIME);
            WA_Lock_Provider::acquire(self::UPDATE_ROLES_LOCK_ID, self::UPDATE_ROLES_LOCK_TIME);

            unset($newRoles[self::FAKE_WA_ROLE_ID]);

            return $this->syncWpRolesWithWaLevels($newRoles);
        }

        return $newRoles;
    }

    public function onRolesOptionUpdateComplete()
    {
        if (WA_Utils::isNotEmptyArray($this->waLevelsToSync))
        {
            $this->waLevelsToSync = null;
            $this->wpRoles->reinit();

            WA_Lock_Provider::release(self::UPDATE_ROLES_LOCK_ID);
        }
    }

    public function syncWaRoles($waLevels)
    {
        if (is_wp_error($waLevels) || !WA_Utils::isNotEmptyArray($waLevels) || !$this->module->isWaLevelValid($waLevels[0]))
        {
            return WA_Error_Handler::handleError('wa_integration_sync_error', $this->module->getUpdateRolesErrorMessage());
        }

        $this->waLevelsToSync = $waLevels;
        add_role(self::FAKE_WA_ROLE_ID, self::FAKE_WA_ROLE_ID);

        return true;
    }

    private function syncWpRolesWithWaLevels(array $wpRoles)
    {
        $defaultUserRole = $this->module->getWaDefaultRole();
        $defaultCapabilities = $defaultUserRole->capabilities;

        foreach ($this->waLevelsToSync as $waRole)
        {
            $waRoleId = $this->module->getWaRoleId($waRole->Id);
            $waRoleName = WA_Utils::sanitizeString($waRole->Name);

            if (!isset($wpRoles[$waRoleId]))
            {
                $wpRoles[$waRoleId] = array
                (
                    'name' => $waRoleName,
                    'capabilities' => $defaultCapabilities
                );

                continue;
            }

            $wpRoles[$waRoleId]['name'] = $waRoleName;
        }

        return $wpRoles;
    }

    private function getWpRoles()
    {
        global $wp_roles;

        if (!isset($wp_roles))
        {
            $wp_roles = new WP_Roles();
        }

        return $wp_roles;
    }
}