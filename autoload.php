<?php

spl_autoload_register('autoloader');

function autoloader($className) {
    if(strpos($className, '\\') !== false) {
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    }

    if(strpos($className, '_') !== false) {
        $className = str_replace('_', DIRECTORY_SEPARATOR, $className);
    }

    require_once($className.'.php');
}
