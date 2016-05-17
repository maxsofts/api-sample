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
        $path = self::__get('path');

        $splitPath = explode('.', $file);

        if($splitPath[0]){
            $file = $path.DIRECTORY_SEPARATOR.$splitPath[0].".php";

            if(file_exists($file)){
                /** @var include config $file */
                $config = include_once $file;

                $countPath = count($splitPath);


                if($countPath > 1){
                    $i = 1;
                    $return = $config;
                    while($i < $countPath){
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


    /**
     *
     * @param $name
     * @return null|static
     */
    static public function __get($name)
    {
        return isset(self::$$name) ? self::$$name : null;
    }
}