<?php

class WA_Modules_Base_WaContact_MetaData
{
    const ID_META_NAME = 'wa_contact_id';
    const OPTION_NAME = 'wa_contact_metadata';

    private $waContactId;
    private $wpUser;
    private $options;
    private $fields = array('contactId', 'organization', 'membershipStatus', 'levelId');
    private $activeMembershipStatuses = array('Active', 'PendingRenewal');

    public function __construct($waContactId = 0, $wpUser = null)
    {
        $this->waContactId = $waContactId;
        $this->wpUser = $wpUser;

        if (empty($this->waContactId) && !($wpUser instanceof WP_User))
        {
            throw new Exception('Input params is invalid.');
        }

        if (empty($this->waContactId))
        {
            $this->setWaContactId();
        }

        if (!($this->wpUser instanceof WP_User))
        {
            $this->setWpUser();
        }

        if ($this->isValid())
        {
            $this->options = new WA_Modules_Base_WaContact_MetaDataOptions(self::OPTION_NAME, $this->wpUser->ID);
        }
    }

    public function isValid()
    {
        return !empty($this->waContactId) && $this->wpUser instanceof WP_User;
    }

    public function getWpUser()
    {
        return $this->wpUser;
    }

    public function isMembershipStatusActive()
    {
        return in_array($this->membershipStatus, $this->activeMembershipStatuses);
    }

    public function __set($key, $value)
    {
        if (!in_array($key, $this->fields))
        {
            throw new Exception('Invalid key.');
        }

        if (!$this->isValid()) { return; }

        $this->options->setOption($this->waContactId, $key, $value);
    }

    public function __get($key)
    {
        if (!in_array($key, $this->fields))
        {
            throw new Exception('Invalid key.');
        }

        if (!$this->isValid()) { return null; }

        return $this->options->getOption($this->waContactId, $key);
    }

    public function save()
    {
        if (!$this->isValid()) { return false; }

        $result = update_user_meta($this->wpUser->ID, self::ID_META_NAME, $this->waContactId);

        return $this->options->save() && $result;
    }

    private function setWaContactId()
    {
        $this->waContactId = get_user_meta($this->wpUser->ID, self::ID_META_NAME, true);
    }

    private function setWpUser()
    {
        $wpUsers = get_users(array('meta_key' => self::ID_META_NAME, 'meta_value' => $this->waContactId, 'count_total' => false));

        if (WA_Utils::isNotEmptyArray($wpUsers) && count($wpUsers) == 1 && $wpUsers[0] instanceof WP_User)
        {
            $this->wpUser = $wpUsers[0];
        }
    }
}