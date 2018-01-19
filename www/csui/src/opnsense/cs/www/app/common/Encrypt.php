<?php

class Encrypt
{
    private static $KEY = 'CSG2000P_ENC_KEY';

    public static function encrypt($code){
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(self::$KEY), $code, MCRYPT_MODE_ECB,
            mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    }

    public static function decrypt($code){
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(self::$KEY), base64_decode($code), MCRYPT_MODE_ECB,
            mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
    }
}