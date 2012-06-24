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

use MongoAppKit\Config;

class Storage {

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
     * Sets up MongoDB connection and selects the given database
     *
     * @param Config
     */

    public function __construct(Config $oConfig) {
        $sMongoServer = $oConfig->getProperty('MongoServer');
        $iMongoPort = $oConfig->getProperty('MongoPort');
        $sMongoUser = $oConfig->getProperty('MongoUser');
        $sMongoPassword = $oConfig->getProperty('MongoPassword');

        $sMongoIdentification = (!empty($sMongoUser) && !empty($sMongoPassword)) ? "{$sMongoUser}:{$sMongoPassword}@" : '';

        $sHost = "mongodb://{$sMongoIdentification}{$sMongoServer}:{$iMongoPort}";
        $this->_oMongo = new \Mongo($sHost);

        $this->_oDatabase = $this->_oMongo->selectDB($oConfig->getProperty('MongoDatabase'));      
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