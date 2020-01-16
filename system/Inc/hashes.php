<?php

namespace Moorexa;

class Hash
{
    protected $domain = null;

    public function __construct()
    {
        $this->domain = url();
    }

    final protected function _hash($string, $size = null, $al = null)
    {
        $this->domain = url();

        // secret key
        $key = Bootloader::boot('secret_key');
        
        // encryption method
        $method = "AES-256-CBC";


        if (function_exists('password_hash'))
        {
            $secret_iv = password_hash($string . time() . $this->domain, PASSWORD_BCRYPT);
        }
        else
        {
            $secret_iv = crypt($string . time() . $this->domain, CRYPT_BLOWFISH);
        }

        // get key
        $key = hash('sha256', $key);

        // iv
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        // encrypt data;
        $encrypt = openssl_encrypt($string, $method, $key, 0, $iv);
        
        
        return $iv .'='. $encrypt;
    }

    final protected function _hashVal($string)
    {
        // secret key
        $key = Bootloader::boot('secret_key');
        
        // encryption method
        $method = "AES-256-CBC";

        $iv = substr($string, 0, strpos($string, '='));
        $string = substr($string, strpos($string, '=')+1);

        // get key
        $key = hash('sha256', $key);

        $decrypt = openssl_decrypt($string, $method, $key, 0, $iv);
        
        return $decrypt;
    }

    public function getKey($key)
    {
        $str = $key;

        if (is_null($this->domain))
        {
            $this->domain = url();
        }

        if (function_exists('password_hash'))
        {
            $key = password_hash($key . $this->domain, PASSWORD_BCRYPT);
        }
        else
        {
            $key = crypt($key . $this->domain, CRYPT_BLOWFISH);
        }

        $key = str_replace('$','', $key);

        $rev = strrev($str);
        $half = (int) ceil(strlen($key)/2);
        $after = substr($key, $half);
        $before = substr($key, 0, $half);

        $newkey = $before.'$'.$rev.'$'.$after;
        return $newkey;
    }

    /**
     * @param $value : string
     */
    public static function digest($value, &$hashValue = null)
    {
        // secret key
        $key = \Moorexa\Bootloader::boot('secret_key');
        
        // generate moorexa salt
        $moorexaSalt = hash('sha256', md5($value) . '\\' . $key . '\\' . 'c0033768e0b8968fc58b50cdca6852c46e4eda39f9153643726b3a120cfb7b09');

        // get password strength
        $len = strlen($value);
        $len = $len > 26 ? 26 : $len;

        // add extra characters
        $char = range('A','Z');
        // add digits
        $digits = range(1, ($len == 1 ? 2 : $len));

        // make string
        $string = '$'.implode("", array_splice($char, 0, $len)) . '$' . implode("", array_splice($digits, 0, $len));

        // new value
        $value = $string.'$'.$value . 'salt:'. $moorexaSalt;

        $hashValue = $value;
        
        // hash value with password_hash or crypt
        if (function_exists('password_hash'))
        {
            return password_hash($value, PASSWORD_BCRYPT);
        }
        
        return crypt($value, CRYPT_BLOWFISH);
        
    }

    /**
     * @param $value : string
     * @param $hashed : string
     */

    public static function verify( $value,  $hashed)
    {
        $digest = self::digest($value, $hashValue);

        if (function_exists('password_hash'))
        {
            if (password_verify($hashValue, $hashed))
            {
                return true;
            }
        }

        if ($digest == $hashed)
        {
            return true;
        }

        return false;
    }

    public static function __callStatic( $meth, $args)
    {

    }
}