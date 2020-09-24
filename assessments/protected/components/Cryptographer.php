<?php

class Cryptographer {

    public static function encrypt($msg, $k, $base64 = false) {
        if (!$td = mcrypt_module_open('rijndael-256', '', 'cbc', ''))
            return false;
        $msg = serialize($msg); # serialize
        $iv = mcrypt_create_iv(32, MCRYPT_RAND); # create iv
        if (mcrypt_generic_init($td, $k, $iv) !== 0) # initialize buffers
            return false;
        $msg = mcrypt_generic($td, $msg); # encrypt
        $msg = $iv . $msg; # prepend iv
        $mac = self::pbkdf2($msg, $k); # create mac
        $msg .= $mac; # append mac
        mcrypt_generic_deinit($td); # clear buffers
        mcrypt_module_close($td); # close cipher module
        if ($base64)
            $msg = rawurlencode(base64_encode($msg));# base64 encode?
        return $msg;
    }

    public static function decrypt($msg, $k, $base64 = false) {
        if ($base64) {
            $msg = rawurldecode($msg);
            $msg = base64_decode($msg); # base64 decode?
        }
        if (!$td = mcrypt_module_open('rijndael-256', '', 'cbc', ''))
            return false;
        $iv = substr($msg, 0, 32); # extract iv
        $mo = strlen($msg) - 32; # mac offset
        $em = substr($msg, $mo); # extract mac
        $msg = substr($msg, 32, strlen($msg) - 64); # extract ciphertext
        $mac = self::pbkdf2($iv . $msg, $k); # create mac
        if ($em !== $mac) # authenticate mac
            return false;
        if (mcrypt_generic_init($td, $k, $iv) !== 0) # initialize buffers
            return false;
        $msg = mdecrypt_generic($td, $msg); # decrypt
        $msg = unserialize($msg); # unserialize
        mcrypt_generic_deinit($td); # clear buffers
        mcrypt_module_close($td); # close cipher module
        return $msg; # return original msg
    }

    /*
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     * $algorithm - The hash algorithm to use. Recommended: SHA256
     * $password - The password.
     * $salt - A salt that is unique to the password.
     * $count - Iteration count. Higher is better, but slower. Recommended: At least 1024.
     * $key_length - The length of the derived key in bytes.
     * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
     * Returns: A $key_length-byte key derived from the password and salt.
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by defuse.ca
     * With improvements by variations-of-shadow.com
     */

    static function pbkdf2($salt, $password, $count = 1000, $key_length = 32, $algorithm = 'sha256') {
        $algorithm = strtolower($algorithm);
        if (!in_array($algorithm, hash_algos(), true))
            die('PBKDF2 ERROR: Invalid hash algorithm.');
        if ($count <= 0 || $key_length <= 0)
            die('PBKDF2 ERROR: Invalid parameters.');
        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);
        $output = "";
        for ($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }
        return substr($output, 0, $key_length);
    }

    /*
     * The pseudorandom function used by PBKDF2.
     * Definition: https://www.ietf.org/rfc/rfc2898.txt
     */

    static function pbkdf2_f($password, $salt, $count, $i, $algorithm, $hLen) {
        //$i encoded as 4 bytes, big endian.
        $last = $salt . chr(($i >> 24) % 256) . chr(($i >> 16) % 256) . chr(($i >> 8) % 256) . chr($i % 256);
        $xorsum = "";
        for ($r = 0; $r < $count; $r++) {
            $u = hash_hmac($algorithm, $last, $password, true);
            $last = $u;
            if (empty($xorsum))
                $xorsum = $u;
            else {
                for ($c = 0; $c < $hLen; $c++) {
                    $xorsum[$c] = chr(ord(substr($xorsum, $c, 1)) ^ ord(substr($u, $c, 1)));
                }
            }
        }
        return $xorsum;
    }

}
