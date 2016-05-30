<?php

namespace max_api\contracts;

class config
{

    static $path = "config";

    /**
     *
     * Get config file
     *
     * @param $file
     * @return mixed|null
     */
    public static function get($file)
    {
        $path = self::$path;

        $splitPath = explode('.', $file);

        if ($splitPath[0]) {
            $filePath = $path . DIRECTORY_SEPARATOR . $splitPath[0] . ".php";

            if (file_exists($filePath)) {
                /** @var include config $file */
                $config = include $filePath;

                $countPath = count($splitPath);


                if ($countPath > 1) {
                    $i = 1;
                    $return = $config;
                    while ($i < $countPath) {
                        $return = $return[$splitPath[$i]];
                        $i++;
                    }

                    return $return;
                }

                return $config;
            }
        }
        return null;
    }


}