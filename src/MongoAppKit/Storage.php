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

    protected $_mongo = null;

    /**
     * MongoDB object
     * @var MongoDB
     */

    protected $_database = null;

    /**
     * Sets up MongoDB connection and selects the given database
     *
     * @param Config
     */

    public function __construct(Config $config) {
        $mongoServer = $config->getProperty('MongoServer');
        $mongoPort = $config->getProperty('MongoPort');
        $mongoUser = $config->getProperty('MongoUser');
        $mongoPassword = $config->getProperty('MongoPassword');

        $mongoIdentification = (!empty($mongoUser) && !empty($mongoPassword)) ? "{$mongoUser}:{$mongoPassword}@" : '';

        $host = "mongodb://{$mongoIdentification}{$mongoServer}:{$mongoPort}";
        $this->_mongo = new \Mongo($host);

        $this->_database = $this->_mongo->selectDB($config->getProperty('MongoDatabase'));
    }

    /**
     * Closes MongoDB connection if the object is destroyed
     */

    public function __destruct() {
        $this->_mongo->close();
    }

    /**
     * Returns MongoDB object of selected Database
     *
     * @return MongoDB
     */

    public function getDatabase() {
        return $this->_database;
    }
}