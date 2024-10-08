<?php

class WA_Modules_ContentRestriction_Module extends WA_Modules_Base_Module
{
    const SHORT_CODE_NAME = 'wa_restricted';

    public function activate(WA_Modules_Interfaces_ICore $core, array $args = null)
    {
        parent::activate($core, $args);

        $shortCode = new WA_Modules_ContentRestriction_ShortCode(
            $this,
            self::SHORT_CODE_NAME,
            array('accessDeniedMessage' => __('You are not authorized to access the requested content.', WaIntegrationPlugin::TEXT_DOMAIN))
        );
    }

    public function getWaLoginForm(array $attr = array())
    {
        return $this->core->getWaLoginForm($attr);
    }

    public function getWaDefaultRole()
    {
        return $this->core->getWaDefaultRole();
    }

    public function getWaRoleId($waLevelId)
    {
        return $this->core->getWaRoleId($waLevelId);
    }
} 