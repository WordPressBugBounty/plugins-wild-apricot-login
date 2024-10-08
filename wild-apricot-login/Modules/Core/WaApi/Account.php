<?php

class WA_Modules_Core_WaApi_Account
{
    private static $dataMap = array
    (
        'Id' => 'integer',
        'Url' => 'string',
        'PrimaryDomainName' => 'string'
    );

    private $Id;
    private $Url;
    private $PrimaryDomainName;
    private $valid = false;
    private $error;

    public function __construct($accountData)
    {
        if (!is_array($accountData) || empty($accountData['body']))
        {
            $this->setError($accountData);
            return;
        }

        $accountDetails = json_decode($accountData['body']);

        if (!WA_Utils::isNotEmptyArray($accountDetails) || !is_object($accountDetails[0]))
        {
            $this->setError($accountDetails);
            return;
        }

        $accountDatObj = $accountDetails[0];

        foreach (self::$dataMap as $property => $type)
        {
            if (!isset($accountDatObj->$property) || gettype($accountDatObj->$property) !== $type || empty($accountDatObj->$property))
            {
                $this->setError($accountDatObj);
                return;
            }

            $this->$property = $this->sanitizeData($accountDatObj->$property, $type);
        }

        $this->valid = true;
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function getId()
    {
        return $this->Id;
    }

    public function getUrl()
    {
        return $this->Url;
    }

    public function getPrimaryDomainName()
    {
        return $this->PrimaryDomainName;
    }

    private function sanitizeData($value, $type)
    {
        switch($type)
        {
            case 'string':
            {
                return WA_Utils::sanitizeString($value);
            }

            case 'integer':
            {
                return WA_Utils::sanitizeInt($value);
            }

            default:
            {
                throw new Exception('Invalid data type.');
            }
        }
    }

    private function setError($data)
    {
        $this->valid = false;

        if (empty($data))
        {
            $this->error = 'Unknown error.';
            return;
        }

        if (is_object($data))
        {
            if (is_wp_error($data))
            {
                $this->error = WA_Utils::sanitizeString($data->get_error_message());
                return;
            }

            if (!empty($data->error))
            {
                $this->error = $this->setError($data->error);
                return;
            }

            return;
            $this->error = $data;
        }
        else if (is_array($data))
        {
            return;
            $this->error = $data;
        }
        else
        {
            $this->error = WA_Utils::sanitizeString($data);
        }
    }
}