<?php

namespace App\Helpers;

class Security{

    /* function to encrypt
    * @param string $data
    * @param string $key
    */
   static function encrypt($data)
   {
        $key = 'seguridad_ante_todo_666_claro_que_si';

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted=openssl_encrypt($data, "aes-256-cbc", $key, 0, $iv);
        // return the encrypted string with $iv joined 
        return base64_encode($encrypted."::".$iv);
   }
    
   /**
    * function to decrypt
    * @param string $data
    * @param string $key
    */
   static function decrypt($data)
   {
        $key = 'seguridad_ante_todo_666_claro_que_si';

        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
   }

}



