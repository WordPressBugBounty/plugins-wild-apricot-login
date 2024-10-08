<?php

class WA_Modules_Base_Crypt_OpenSslAES
{
    const ENC_METHOD = 'AES-128-CFB';
    const IS_RAW = true;

    private $salt;
    private $iv;

    public function __construct(array $config)
    {
        $this->salt = $config['salt'];
        $this->iv = substr(md5($config['password']), 0, openssl_cipher_iv_length(self::ENC_METHOD));
    }

    public function encrypt($text)
    {
        return openssl_encrypt($text, self::ENC_METHOD, $this->salt, self::IS_RAW, $this->iv);
    }

    public function decrypt($text)
    {
        return openssl_decrypt($text, self::ENC_METHOD, $this->salt, self::IS_RAW, $this->iv);
    }
}