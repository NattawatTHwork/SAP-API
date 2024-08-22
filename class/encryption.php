<?php
class Encryption
{
    private $key;
    private $cipher;

    public function __construct()
    {
        $this->key = 'zi8F9GMkZgaCfqpn2pFG';
        $this->cipher = 'aes-128-cbc';
    }

    public function encrypt($data)
    {
        $iv = str_repeat('0', openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        return base64_encode($encrypted);
    }

    public function decrypt($data)
    {
        $iv = str_repeat('0', openssl_cipher_iv_length($this->cipher));
        $encrypted_data = base64_decode($data);
        return openssl_decrypt($encrypted_data, $this->cipher, $this->key, 0, $iv);
    }
}
