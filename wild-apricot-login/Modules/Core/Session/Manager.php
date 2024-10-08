<?php

class WA_Modules_Core_Session_Manager implements WA_Modules_Interfaces_ISessionManager
{
    const DATA_DELIMITER = '||';

    private $data;
    private $sessionId;
    private $expiresIn;
    private $expirationTime;
    private $expirationOffset;

    public function __construct($sessionName, $expirationTime, $expirationOffset)
    {
        $this->expirationTime = $expirationTime;
        $this->expirationOffset = $expirationOffset;
        $this->setSessionParams($sessionName);
        $this->data = new WA_Modules_Core_Session_Data($sessionName . '_' . $this->sessionId, $expirationTime);
        $this->setExpiration();
        setcookie($sessionName, implode(self::DATA_DELIMITER, array($this->sessionId, $this->expiresIn)), $this->expiresIn, COOKIEPATH, COOKIE_DOMAIN);
    }

    public function get($moduleId, $key)
    {
        return $this->data->getOption($moduleId, $key);
    }

    public function set($moduleId, $key, $value)
    {
        return $this->data->setOption($moduleId, $key, $value);
    }

    public function remove($moduleId, $key)
    {
        return $this->data->unsetOption($moduleId, $key);
    }

    private function setSessionParams($sessionName)
    {
        if (isset($_COOKIE[$sessionName]))
        {
            $data = WA_Utils::sanitizeString($_COOKIE[$sessionName]);
            $parts = explode(self::DATA_DELIMITER, $data);

            $this->sessionId = $parts[0];
            $this->expiresIn = WA_Utils::sanitizeInt($parts[1]);
        }
        else
        {
            $this->sessionId = $this->generateId();
        }
    }

    private function setExpiration()
    {
        $now = time();

        if (empty($this->expiresIn))
        {
            $this->expiresIn = $now + $this->expirationTime;
        }

        if ($now > $this->expiresIn - $this->expirationOffset)
        {
            $this->expiresIn = $now + $this->expirationTime;
            $this->data->renew();
        }
    }

    private function generateId()
    {
        require_once(ABSPATH . 'wp-includes/class-phpass.php');

        $idHash = new PasswordHash(8, false);

        return md5($idHash->get_random_bytes(32));
    }
}