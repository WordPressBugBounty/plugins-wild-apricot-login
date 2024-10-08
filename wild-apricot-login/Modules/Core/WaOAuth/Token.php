<?php

class WA_Modules_Core_WaOAuth_Token
{
    private static $dataMap = array
    (
        'access_token' => 'string',
        'token_type' => 'string',
        'expires_in' => 'integer',
        /*'refresh_token' => 'string'/*,
        'actual_scope' => 'array'*/
    );

    private $error;
    private $access_token;
    private $token_type;
    private $expires_in;
    private $refresh_token;
    private $actual_scope;
    private $created;
    private $valid = false;


    public function __construct($apiKey, $tokenData)
    {
        $this->created = time();
        $this->apiKey = $apiKey;

        if (!is_array($tokenData) || empty($tokenData['body']))
        {
            $this->setError($tokenData);
            return;
        }

        $this->initData(json_decode($tokenData['body']));
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function isExpired()
    {
        return $this->valid && time() > $this->created + $this->expires_in;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getAuthorizationHeader()
    {
        return $this->token_type . ' ' . $this->access_token;
    }

    private function initData($tokenDataObj)
    {
        if (!is_object($tokenDataObj))
        {
            $this->setError($tokenDataObj);
            return;
        }

        foreach (self::$dataMap as $property => $type)
        {
            if (!isset($tokenDataObj->$property) || gettype($tokenDataObj->$property) !== $type || empty($tokenDataObj->$property))
            {
                $this->setError($tokenDataObj);
                return;
            }

            $this->$property = $this->sanitizeData($tokenDataObj->$property, $type);
        }

        $this->valid = true;
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

            case 'array':
            {
                return WA_Utils::sanitizeArray($value, 'string');
            }

            default:
            {
                throw new Exception('Invalid data type.');
            }
        }
    }

    private function setError($tokenData)
    {
        $this->valid = false;

        if (empty($tokenData))
        {
            $this->error = 'Unknown error.';
            return;
        }

        if (is_object($tokenData))
        {
            if (is_wp_error($tokenData))
            {
                $this->error = WA_Utils::sanitizeString($tokenData->get_error_message());
                return;
            }

            if (!empty($tokenData->error))
            {
                $this->error = $this->setError($tokenData->error);
                return;
            }

            return;
            $this->error = $tokenData;
        }
        else if (is_array($tokenData))
        {
            return;
            $this->error = $tokenData;
        }
        else
        {
            $this->error = WA_Utils::sanitizeString($tokenData);
        }
    }
}