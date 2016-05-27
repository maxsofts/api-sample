<?php
namespace max_api\contracts;

class uploadHelper
{

    public static function linkToDir($link)
    {
        $config = config::get("sftp");

        $imageSplit = explode("/", $link);

        $imageName = end($imageSplit);

        $dir = sprintf($config['upload']['dir_base'], $imageSplit[5], $imageSplit[6]);

        $imageDir = $dir . $imageName;

        return $imageDir;
    }
}