<?php

class WA_Modules_ContentRestriction_ShortCode extends WA_Modules_Base_ShortCode
{
    const EXCLUDE_LOGIC_PREFIX = 'not:';
    const ROLES_DELIMITER = ',';
    const ENCODED_ROLES_DELIMITER = '%2c';
    const ROLES_ID = 'roles';
    const ROLES_MAX_LENGTH = 4096;
    const MESSAGE_ID = 'message';
    const MESSAGE_MAX_LENGTH = 4096;
    const LOGIN_LABEL_ID = 'login_label';
    const ALL_ROLES = 'all';

    private $currentUserRoles;
    protected $attributes = array(self::ROLES_ID, self::LOGIN_LABEL_ID, self::MESSAGE_ID);
    protected $defaults = array(
        self::ROLES_ID => '',
        self::LOGIN_LABEL_ID => '',
        self::MESSAGE_ID => ''
    );

    public function __construct(WA_Modules_Interfaces_IModule $module, $shortCodeName, array $args = null)
    {
        parent::__construct($module, $shortCodeName, $args);

        $this->formTitle = __('Restrict content to Wild Apricot members', WaIntegrationPlugin::TEXT_DOMAIN);
        $this->buttonImgUrl = $module->getImageUrl('wa_restricted_shortcode_editor_button.png');
        $this->formFields = array(
            array(
                'type' => 'textbox',
                'name' => self::ROLES_ID,
                'label' => __('Roles', WaIntegrationPlugin::TEXT_DOMAIN),
                'tooltip' => __('e.g. Gold, Silver, Bronze. Add a "' . self::EXCLUDE_LOGIC_PREFIX . '" at the start to specify all roles other than those listed.', WaIntegrationPlugin::TEXT_DOMAIN)
            ),
            array(
                'type' => 'textbox',
                'name' => self::LOGIN_LABEL_ID,
                'label' => __('Login button label', WaIntegrationPlugin::TEXT_DOMAIN),
                'tooltip' => __('Leave blank for default from the Wild Apricot Login plugin settings', WaIntegrationPlugin::TEXT_DOMAIN)
            ),
            array(
                'type' => 'textbox',
                'name' => self::MESSAGE_ID,
                'label' => __('Restricted content message', WaIntegrationPlugin::TEXT_DOMAIN),
                'tooltip' => __('Leave blank for default "' . $this->args['accessDeniedMessage'] . '"', WaIntegrationPlugin::TEXT_DOMAIN),
                'multiline' => true,
                'minWidth' => 300,
                'minHeight' => 100
            )
        );
        $this->isContentWrapper = true;
    }

    public function render($attributes, $content, $shortCodeName)
    {
        $this->disablePageCache();

        $attr = shortcode_atts($this->defaults, $attributes, $shortCodeName);

        if (!is_user_logged_in())
        {
            return WA_Utils::sanitizeString($attr[self::MESSAGE_ID], self::MESSAGE_MAX_LENGTH, true)
                . $this->module->getWaLoginForm(array(self::LOGIN_LABEL_ID => WA_Utils::sanitizeString($attr[self::LOGIN_LABEL_ID]))
            );
        }

        $rolesAttr = wp_specialchars_decode(WA_Utils::sanitizeString($attr[self::ROLES_ID], self::ROLES_MAX_LENGTH));

        if (empty($rolesAttr)) { return $this->args['accessDeniedMessage']; }

        if (!WA_Utils::isNotEmptyArray($this->currentUserRoles))
        {
            $this->currentUserRoles = $this->getCurrentUserRoles();
        }

        if (!WA_Utils::isNotEmptyArray($this->currentUserRoles)) { return $this->args['accessDeniedMessage']; }

        $attrRoles = $this->getAttrRoles($rolesAttr);

        if (!WA_Utils::isNotEmptyArray($attrRoles)) { return $this->args['accessDeniedMessage']; }

        $suitableRoles = array_intersect($this->currentUserRoles, $attrRoles);

        return (count($suitableRoles) > 0) ? do_shortcode($content) : $this->args['accessDeniedMessage'];
    }

    public function normalizeRoleName($roleName)
    {
        return strtolower(trim($roleName));
    }

    public function normalizeAttrRoleName($roleName)
    {
        return str_replace(self::ENCODED_ROLES_DELIMITER, self::ROLES_DELIMITER, trim($roleName));
    }

    private function getCurrentUserRoles()
    {
        $wpUser = wp_get_current_user();

        if (!WA_Utils::isNotEmptyArray($wpUser->roles)) { return $wpUser->roles; }

        $waContactMetaData = new WA_Modules_Base_WaContact_MetaData(0, $wpUser);

        if (!$waContactMetaData->isValid()) { return $wpUser->roles; }

        $levelId = $waContactMetaData->levelId;

        if (empty($levelId) || $waContactMetaData->isMembershipStatusActive()) { return $wpUser->roles; }

        $roles = array();

        foreach ($wpUser->roles as $roleId)
        {
            if ($roleId == $this->module->getWaRoleId($levelId))
            {
                $waDefaultRole = $this->module->getWaDefaultRole();
                $roleId = $waDefaultRole->name;
            }

            $roles[] = $roleId;
        }

        return array_unique($roles);
    }

    private function getAttrRoles($rolesAttr)
    {
        $rolesAttr = strtolower($rolesAttr);
        $excludeLogicPrefix = strtolower(self::EXCLUDE_LOGIC_PREFIX);
        $excludeLogicPrefixLength = strlen($excludeLogicPrefix);

        if (substr($rolesAttr, 0, $excludeLogicPrefixLength) == $excludeLogicPrefix)
        {
            $rolesAttr = substr($rolesAttr, $excludeLogicPrefixLength);
            $isExcludeLogic = true;
        }
        else
        {
            $isExcludeLogic = false;
        }

        $attrRolesNames = explode(self::ROLES_DELIMITER, $rolesAttr);

        if (!WA_Utils::isNotEmptyArray($attrRolesNames)) { return array(); }

        $attrRolesNames = array_map(array($this, 'normalizeAttrRoleName'), $attrRolesNames);

        return $this->getAttrRolesByNames($attrRolesNames, $isExcludeLogic);
    }

    private function getAttrRolesByNames($attrRolesNames, $isExcludeLogic)
    {
        $wpRoles = $this->getWpRoles();

        if (!WA_Utils::isNotEmptyArray($wpRoles->role_names)) { return array(); }

        $wpRolesNames = array_map(array($this, 'normalizeRoleName'), $wpRoles->role_names);

        if (in_array(strtolower(self::ALL_ROLES), $attrRolesNames))
        {
            $attrRolesNames = $wpRolesNames;
        }

        $result = $isExcludeLogic ? array_diff($wpRolesNames, $attrRolesNames) : array_intersect($wpRolesNames, $attrRolesNames);

        $attrRoles = array_keys($result);
        $waDefaultRoleName = $this->normalizeRoleName($this->module->getWaDefaultRole()->name);
        $attrMemberRoles = array_diff($attrRoles, array($waDefaultRoleName));

        return array_values($attrMemberRoles);
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