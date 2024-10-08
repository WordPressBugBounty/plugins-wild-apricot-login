<?php

class WA_Modules_Synchronization_UserSynchronizer
{
    private $module;

    public function __construct(WA_Modules_Synchronization_Module $module)
    {
        $this->module = $module;
    }

    public function syncWaContact($waContact)
    {
        if
        (
            isset($waContact->MembershipLevel)
            && $this->module->isWaLevelValid($waContact->MembershipLevel)
            && $this->module->syncWaRoles(array($waContact->MembershipLevel)) === true
        )
        {
            $userRole = $this->module->getWaRoleId($waContact->MembershipLevel->Id);
        }
        else
        {
            $userRole = $this->module->getWaDefaultRole()->name;
        }

        $wpUser = $this->module->getWpUserByWaContact($waContact);

        if (!($wpUser instanceof WP_User))
        {
            $this->createWpUser($waContact, $userRole);
        }
        else
        {
            if (is_super_admin($wpUser->ID)) { return; }

            $this->updateWpUser($wpUser, $waContact, $userRole);
        }
    }

    private function createWpUser($waContact, $userRole)
    {
        $userLogin = $this->module->getWaUserLogin($waContact->Id);

        $newUserId = wp_insert_user
        (
          array
          (
              'user_pass' => wp_generate_password(),
              'user_login' => $userLogin,
              'user_nicename' => $userLogin,
              'user_email' => WA_Utils::sanitizeEmail($waContact->Email),
              'display_name' => empty($waContact->DisplayName) ? '' : WA_Utils::sanitizeString($waContact->DisplayName),
              'nickname' => empty($waContact->DisplayName) ? '' : WA_Utils::sanitizeString($waContact->DisplayName),
              'first_name' => empty($waContact->FirstName) ? '' : WA_Utils::sanitizeString($waContact->FirstName),
              'last_name' => empty($waContact->LastName) ? '' : WA_Utils::sanitizeString($waContact->LastName),
              'role' => $userRole
          )
        );

        if (is_wp_error($newUserId)) { return; }

        $wpUser = get_user_by('id', $newUserId);

        if ($wpUser)
        {
            $this->updateWaMeta($wpUser, $waContact);
        }
    }

    private function updateWpUser($wpUser, $waContact, $userRole)
    {
        $userId = wp_update_user
        (
            array
            (
                'ID' => $wpUser->ID,
                'user_email' => WA_Utils::sanitizeEmail($waContact->Email),
                'display_name' => empty($waContact->DisplayName) ? '' : WA_Utils::sanitizeString($waContact->DisplayName),
                'nickname' => empty($waContact->DisplayName) ? '' : WA_Utils::sanitizeString($waContact->DisplayName),
                'first_name' => empty($waContact->FirstName) ? '' : WA_Utils::sanitizeString($waContact->FirstName),
                'last_name' => empty($waContact->LastName) ? '' : WA_Utils::sanitizeString($waContact->LastName),
                'role' => $userRole
            )
        );

        if (!is_wp_error($userId))
        {
            $this->updateWaMeta($wpUser, $waContact);
        }
    }

    private function updateWaMeta($wpUser, $waContact)
    {
        $waContactId = WA_Utils::sanitizeString($waContact->Id);
        $waContactMetaData = new WA_Modules_Base_WaContact_MetaData($waContactId, $wpUser);

        $waContactMetaData->contactId = WA_Utils::sanitizeString($waContact->Id);

        if (isset($waContact->Organization))
        {
            $waContactMetaData->organization = WA_Utils::sanitizeString($waContact->Organization);
        }

        if (isset($waContact->Status))
        {
            $waContactMetaData->membershipStatus = WA_Utils::sanitizeString($waContact->Status);
        }

        if (isset($waContact->MembershipLevel) && $this->module->isWaLevelValid($waContact->MembershipLevel))
        {
            $waContactMetaData->levelId = WA_Utils::sanitizeKey($waContact->MembershipLevel->Id);
        }

        $waContactMetaData->save();
    }
}