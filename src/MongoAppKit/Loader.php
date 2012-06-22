<?php

namespace MongoAppKit;

class Loader {
    
    public static function registerAutoloader() {
        return spl_autoload_register(array ('MongoAppKit\\Loader', 'load'));
    }

    public static function load($sClassName) {
        if(substr($sClassName, 0, 11) !== 'MongoAppKit') {
            return;
        }

        $sLibraryRoot = realpath(__DIR__ . '/../');
        $sFileName = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $sClassName) . '.php';
        $sFileName = realpath($sLibraryRoot . DIRECTORY_SEPARATOR . $sFileName);
        
        if(substr($sFileName, 0, strlen($sLibraryRoot)) == $sLibraryRoot) {            
            if(is_readable($sFileName)) {
                include_once($sFileName);
            }
        }
    }
}