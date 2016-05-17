<?php

namespace max_api;

function load($namespace)
{
    $splitPath = explode('\\', $namespace);
    $path = '';
    $name = '';
    $firstWord = true;

    for ($i = 0; $i < count($splitPath); $i++) {
        if ($splitPath[$i] && !$firstWord) {
            if ($i == count($splitPath) - 1)
                $name = $splitPath[$i];
            else
                $path .= DIRECTORY_SEPARATOR . $splitPath[$i];
        }
        if ($splitPath[$i] && $firstWord) {
            if ($splitPath[$i] != __NAMESPACE__)
                break;
            $firstWord = false;
        }
    }

    if (!$firstWord) {
        $fullPath = __DIR__ . $path . DIRECTORY_SEPARATOR . $name . '.php';

        return include_once($fullPath);
    }
    return false;
}

function loadPath($absPath)
{
    return include_once($absPath);
}


spl_autoload_register(__NAMESPACE__ . '\load');

