<?php

/**
 * Class Document
 *
 * Small implementation of ActiveRecord pattern for MongoDB documents
 * 
 * @author David Henning <madcat.me@gmail.com>
 * 
 * @package MongoAppKit
 */

namespace MongoAppKit\Documents;

use MongoAppKit\Config,
    MongoAppKit\Lists\IterateableList;

use Silex\Application;

class Document extends IterateableList {

    /**
     * MongoDB object
     * @var Application
     */

    protected $_app = null;

    /**
     * MongoDB object
     * @var MongoDB
     */

    protected $_database = null;

    /**
     * Config object
     * @var Config
     */

    protected $_config = null;

    /**
     * MongoCollection object
     * @var MongoCollection
     */

    protected $_collection = null;

    /**
     * Config data for collection
     * @var array
     */

    protected $_collectionConfig = array();

    /**
     * Set MongoDB object
     *
     * @param MongoDB $oDatabase
     */

    public function __construct(Application $app, $collectionName) {
        $this->_app = $app;
        $this->_setDatabase($app['storage']->getDatabase());
        $this->_setConfig($app['config']);
        $this->_setCollection($collectionName);
    }

    public function getDatabase() {
        return $this->_database;
    }

    protected function _setDatabase(\MongoDB $database) {
        $this->_database = $database;
    }

    /**
     * Set Config object
     *
     * @param Config $config
     */

    protected function _setConfig(Config $config) {
        $this->_config = $config;
    }

    public function getCollection() {
        return $this->_collection;
    }

    /**
     * Select MongoDB collection
     *
     * @param string $collectionName
     */

    protected  function _setCollection($collectionName) {
        if(empty($collectionName)) {
            throw new \InvalidArgumentException('Collection name empty');
        }

        $this->_collection = $this->_database->selectCollection($collectionName);
        $this->_loadCollectionConfig($collectionName);
    }

    /**
     * Load collection config from config object and stores it interally
     *
     * @param string $collectionName
     */

    protected function _loadCollectionConfig($collectionName) {
        $propertyConfig = $this->_config->getProperty('Fields');

        if(isset($propertyConfig[$collectionName]) && count($propertyConfig[$collectionName]) > 0) {
            $this->_collectionConfig = $propertyConfig[$collectionName];
        }
    }

    /**
     * Check if given field name exists in config
     *
     * @param string $field
     * @return bool
     */

    public function fieldExists($field) {
        return in_array($field, array_keys($this->_collectionConfig));
    }

    /**
     * Iterate all properties and prepares them for saving in the selected Collection
     *
     * @return array
     */

    public function getPreparedProperties() {
        $preparedProperties = array();

        if(!empty($this->_collectionConfig)) {
            // iterates collection config
            foreach($this->_collectionConfig as $property => $fieldConfig) {
                // get value of property or set to null, if property does not exists or is empty
                $value = (isset($this->_properties[$property])) ? $this->_properties[$property] : null;
                // get prepared property value
                $preparedProperties[$property] = $this->_prepareProperty($property, $value, $fieldConfig);
            }
        }

        return $preparedProperties;
    }

    /**
     * Prepare a proptery for saving
     *
     * @param string $property
     * @param mixed $value
     * @param array $fieldConfig
     * @return mixed
     */

    protected function _prepareProperty($property, $value, $fieldConfig) {
         if(!empty($fieldConfig)) {
            // set Mongo type object
            if(isset($fieldConfig['mongoType'])) {
                $value = $this->_setMongoValueType($fieldConfig['mongoType'], $value);
            }

            // set index
            if(isset($fieldConfig['index']) && $fieldConfig['index'] === true) {
                $this->_getCollection()->ensureIndex($property);
            }

            // encrypt field data
            if(isset($fieldConfig['encrypt']) && $fieldConfig['encrypt'] === true) {
                $value = $this->_app['encryption']->encrypt($value, $this->_config->getProperty('EncryptionKey'));
            }

            // set php type
            if(isset($fieldConfig['phpType'])) {
                $value = $this->_setPhpValueType($fieldConfig['phpType'], $value);
            }           
        }

        return $value; 
    }

    /**
     * Convert value to a Mongo type object
     *
     * @param string $type
     * @param mixed $value
     * @return mixed
     */

    protected function _setMongoValueType($type, $value) {
        switch($type) {
            case 'id':
                if(!$value instanceof \MongoId) {
                    return ($value !== null && !empty($value)) ? new \MongoId($value) : new \MongoId();
                }              
                
                break;
            case 'date':
                if(!$value instanceof \MongoDate) {
                    $value = (!is_int($value)) ? strtotime($value) : $value;
                    return ($value !== null && !empty($value)) ? new \MongoDate($value) : new \MongoDate();
                }              
                
                break;
        }

        return $value;
    }

    /**
     * Type cast to given PHP type
     *
     * @param string $type
     * @param mixed $value
     * @return mixed
     */

    protected function _setPhpValueType($type, $value) {
        switch($type) {
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;                      
            case 'bool':
                return (bool)$value;
            case 'string':
                return (string)$value;
        }

        return $value;
    }

    /**
     * Get MongoCollection object or throws an exception if it's not set
     *
     * @throws Exception
     * @return MongoCollection
     */

    protected function _getCollection() {
        if(!$this->_collection instanceof \MongoCollection) {
            throw new \Exception('No collection selected!');
        }

        return $this->_collection;
    }

    /**
     * Override parent method to perpare certain values (f.e. MongoDate) to return their value
     *
     * @param string $property
     * @return mixed
     */

    public function getProperty($property) {
        $value = parent::getProperty($property);

        // get timestamp of MongoDate object
        if($value instanceof \MongoDate) {
            $value = date('Y-m-d H:i:s', $value->sec);
        }

        // get id of MongoId object
        if($value instanceof \MongoId) {
            $value = $value->{'$id'};
        }

        if(isset($this->_collectionConfig[$property]['encrypt'])) {
            $value = $this->_app['encryption']->decrypt($value, $this->_config->getProperty('EncryptionKey'));
        }

        return $value;
    }

    /**
     * Get id of current document
     *
     * @return string
     */

    public function getId() {
        $this->_setId();
        return $this->_properties['_id']->{'$id'};
    }

    /**
     * Set id of current document if none exists
     */

    protected function _setId() {
        if(!isset($this->_properties['_id']) || !$this->_properties['_id'] instanceof \MongoId) {
            $this->_properties['_id'] = new \MongoId();
        }
    }

    /**
     * Load documend from given id
     *
     * @param string $id
     */

    public function load($id) {
        $data = $this->_getCollection()->findOne(array('_id' => new \MongoId($id)));
        
        if($data === null) {
            throw new \Exception("Document id '{$id}' does not exist!");
        }

        $this->_properties = $data;
    }

    /**
     * Save document properties into the selected MongoDB collection
     */

    public function save() {
        $this->_setId();
        $preparedProperties = $this->getPreparedProperties();
        $this->_properties = $preparedProperties;
        $this->_getCollection()->save($preparedProperties);
    }

    /**
     * Delete current document from the selected MongoDB collection
     */

    public function delete() {
        $this->_getCollection()->remove(array('_id' => $this->_properties['_id']));
    }
}