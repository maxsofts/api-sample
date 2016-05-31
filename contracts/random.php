<?php
namespace max_api\contracts;

class random
{
    public static function render($length = 6)
    {
        $code = '';

        $i = 0;
        while ($i < $length) {
            $code .= rand(0, 9);

            $i++;
        }

        return $code;
    }

    public static function renderString($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}