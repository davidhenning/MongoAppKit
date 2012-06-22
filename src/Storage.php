<?php

/**
 * Class Storage
 *
 * Manages connection to MongoDB
 * 
 * @author David Henning <madcat.me@gmail.com>
 * 
 * @package MongoAppKit
 */

namespace MongoAppKit;

class Storage extends Base {

    /**
     * Storage object
     * @var Storage
     */

    private static $_oInstance = null;

    /**
     * Mongo object
     * @var Mongo
     */

    protected $_oMongo = null;

    /**
     * MongoDB object
     * @var MongoDB
     */

    protected $_oDatabase = null;

    /**
     * Return instance of class Storage
     *
     * @param Config
     * @return Storage
     */

    public static function getInstance() {
        if(self::$_oInstance === null) {
            self::$_oInstance = new Storage();
        }

        return self::$_oInstance;
    }

    /**
     * Sets up MongoDB connection and selects the given database
     *
     * @param Config
     */

    private function __construct() {
        $sMongoServer = $this->getConfig()->getProperty('MongoServer');
        $iMongoPort = $this->getConfig()->getProperty('MongoPort');
        $sMongoUser = $this->getConfig()->getProperty('MongoUser');
        $sMongoPassword = $this->getConfig()->getProperty('MongoPassword');

        $sHost = "mongodb://{$sMongoServer}:{$iMongoPort}";
        $this->_oMongo = new \Mongo($sHost);

        $this->_oDatabase = $this->_oMongo->selectDB($this->getConfig()->getProperty('MongoDatabase'));

        if(!empty($sMongoUser) && !empty($sMongoPassword)) {
            $this->_oDatabase->authenticate($sMongoUser, $sMongoPassword);
        }        
    }

    /**
     * Prohibit cloning of the class object (Singleton pattern)
     */

    public function __clone() {
        return null;
    }

    /**
     * Closes MongoDB connection if the object is destroyed
     */

    public function __destruct() {
        $this->_oMongo->close();
    }

    /**
     * Returns MongoDB object of selected Database
     *
     * @return MongoDB
     */

    public function getDatabase() {
        return $this->_oDatabase;
    }
}