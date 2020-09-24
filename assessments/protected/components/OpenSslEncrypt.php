<?php

class OpenSslEncrypt {

    private static $instance = null;
    private $secret = 'fa72077c783988f2adf56152ac3e2b8a';
    private $iv;

    private function __construct($secret, $iv) {
        if($secret)
            $this->secret = $secret;
        if($iv)
            $this->iv = $iv;
    }

    /**
     * @param null $secret
     * @param null $iv
     *
     * @return OpenSslEncrypt
     */
    public static function getInstance($secret = null, $iv = null) {
        if(self::$instance == null) {
            self::$instance = new OpenSslEncrypt($secret, $iv);
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getSecret() {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret) {
        $this->secret = $secret;
    }

    /**
     * @return mixed
     */
    public function getIv() {
        return $this->iv;
    }

    /**
     * @param mixed $iv
     */
    public function setIv($iv) {
        $this->iv = $iv;
    }

    /**
     * @param $string
     * @param string $method
     *
     * @return string
     */
    public function encrypt($string, $method = "AES-256-CBC") {
        $key = hash('sha256', $this->secret, true);
        $iv = $this->iv ? $this->iv : openssl_random_pseudo_bytes(16);

        $cipher_text = openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv);
        $hash = hash_hmac('sha256', $cipher_text . $iv, $key, true);

        return bin2hex($iv . $hash . $cipher_text);
    }

    /**
     * @param $encrypted_string
     * @param string $method
     *
     * @return bool|false|string
     */
    public function decrypt($encrypted_string, $method = "AES-256-CBC") {
        $iv_hash_cipher_text = @hex2bin($encrypted_string);
        $iv = $this->iv ? $this->iv : substr($iv_hash_cipher_text, 0, 16);
        $hash = substr($iv_hash_cipher_text, 16, 32);
        $cipher_text = substr($iv_hash_cipher_text, 48);
        $key = hash('sha256', $this->secret, true);

        if(!$this->hash_equals(hash_hmac('sha256', $cipher_text . $iv, $key, true), $hash)){
            return false;
        }

        return openssl_decrypt($cipher_text, $method, $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * @param $str1
     * @param $str2
     *
     * @return bool
     */
    private function hash_equals($str1, $str2) {
        if(strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--){
                $ret |= ord($res[$i]);
            }

            return !$ret;
        }
    }

}
