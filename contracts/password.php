<?php

namespace max_api\contracts;

class password
{

    /**
     * Make Password Django
     * @param $password
     * @return string
     */
    public static function make_password($password)
    {
        $algorithm = "pbkdf2_sha256";
        $iterations = 15000;

        $newSalt = mcrypt_create_iv(6, MCRYPT_DEV_URANDOM);
        $newSalt = base64_encode($newSalt);

        $hash = hash_pbkdf2("SHA256", $password, $newSalt, $iterations, 0, true);
        $toDBStr = $algorithm . "$" . $iterations . "$" . $newSalt . "$" . base64_encode($hash);

        // This string is to be saved into DB, just like what Django generate.
        return $toDBStr;
    }

    /**
     *
     * Check password
     *
     * @param $dbString
     * @param $password
     * @return bool
     */
    public static function verify_Password($dbString, $password)
    {

        $pieces = explode("$", $dbString);

        $iterations = $pieces[1];
        $salt = $pieces[2];
        $old_hash = $pieces[3];



        $hash = hash_pbkdf2("SHA256", $password, $salt, $iterations, 0, true);
        $hash = base64_encode($hash);

        if ($hash == $old_hash) {
            // login ok.
            return true;
        } else {
            //login fail
            return false;
        }
    }
}