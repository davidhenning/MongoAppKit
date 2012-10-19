<?php

/**
 * Class Config
 *
 * Reads and stores data from a the config file and provides object access via Singleton pattern
 * 
 * @author David Henning <madcat.me@gmail.com>
 * 
 * @package MongoAppKit
 */

namespace MongoAppKit;

use MongoAppKit\Lists\IterateableList;

class Config extends IterateableList {

    public function addConfigFile($fileName = null) {
        if(empty($fileName)) {
            throw new \InvalidArgumentException("Empty config file name specified.");
        }
        
        if(!is_readable($fileName)) {
            throw new \InvalidArgumentException("File {$fileName} is not readable!");
        }

        $configFileJsonData = file_get_contents($fileName);
        $configData = json_decode($configFileJsonData, true);
        $this->updateProperties($configData);
    }

    public function setBaseDir($baseDir) {
        $this->setProperty('BaseDir', $baseDir);
    }

    public function getBaseDir() {
        return $this->getProperty('BaseDir');
    }

    public function getConfDir() {
        return realpath($this->getBaseDir() . '/conf/');
    }

    public function sanitize($data) {
        if($data === null) {
            return null;
        }

        if(is_array($data)) {
            $sanitizedData = array();
            foreach($data as $key => $value) {
                $sanitizedData[$key] = $this->sanitize($value);
            }

            return $sanitizedData;
        }

        $data = trim($data);
        $data = rawurldecode($data);     
        $data = htmlspecialchars($data);
        $data = strip_tags($data);

        return $data;
    }
}
