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

    public function addConfigFile($sFileName = null) {
        if(empty($sFileName)) {
            throw new \InvalidArgumentException("Empty config file name specified.");
        }

        $configFile = getBasePath().$sFileName;

        if(!is_readable($configFile)) {
            throw new \InvalidArgumentException("File {$sFileName} is not readable!");
        }

        $configFileJsonData = file_get_contents($configFile);
        $configData = json_decode($configFileJsonData, true);
        $this->updateProperties($configData);
    }

    public function sanitize($data) {
        if($data === null) {
            return null;
        }

        if(is_array($data)) {
            $aSanitizedData = array();
            foreach($data as $key => $value) {
                $aSanitizedData[$key] = $this->sanitize($value);
            }

            return $aSanitizedData;
        }

        $data = trim($data);
        $data = rawurldecode($data);     
        $data = htmlspecialchars($data);
        $data = strip_tags($data);

        return $data;
    }
}
