<?php

class WA_Modules_Base_Crypt_McryptAES
{
    private $iv;
    private $salt;
    private $password;

    public function __construct(array $config)
    {
        $this->salt = $config['salt'];
        $this->password = $config['password'];
        $this->iv = $config['iv'];
    }

    public function encrypt($text)
    {
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->salt . $this->password, $text, MCRYPT_MODE_CFB, $this->iv);
    }

    public function decrypt($text)
    {
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->salt . $this->password, $text, MCRYPT_MODE_CFB, $this->iv);
    }

    public static function createIv()
    {
        return mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB), MCRYPT_RAND);
    }
}