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
    MongoAppKit\Lists\IterateableList,
    MongoAppKit\Encryption;

class Document extends IterateableList {

    /**
     * MongoDB object
     * @var MongoDB
     */

    protected $_oDatabase = null;

    /**
     * Config object
     * @var Config
     */

    protected $_oConfig = null;

    /**
     * MongoCollection object
     * @var MongoCollection
     */

    protected $_oCollection = null;

    /**
     * Config data for collection
     * @var array
     */

    protected $_aCollectionConfig = array();

    /**
     * Set MongoDB object
     *
     * @param MongoDB $oDatabase
     */

    public function __construct(Config $oConfig) {
        $this->setDatabase($oConfig->getProperty('storage')->getDatabase());
        $this->setConfig($oConfig);
    }

    public function setDatabase(\MongoDB $oDatabase) {
        $this->_oDatabase = $oDatabase;
    }

    /**
     * Set Config object
     *
     * @param Config $oConfig
     */

    public function setConfig(Config $oConfig) {
        $this->_oConfig = $oConfig;
    }

    /**
     * Select MongoDB collection
     *
     * @param string $sCollectionName
     */

    public function setCollectionName($sCollectionName) {
        if(empty($sCollectionName)) {
            throw new \InvalidArgumentException('Collection name empty');
        }

        $this->_oCollection = $this->_oDatabase->selectCollection($sCollectionName);
        $this->_loadCollectionConfig($sCollectionName);
    }

    /**
     * Load collection config from config object and stores it interally
     *
     * @param string $sCollectionName
     */

    protected function _loadCollectionConfig($sCollectionName) {
        $aPropertyConfig = $this->_oConfig->getProperty('Fields');

        if(isset($aPropertyConfig[$sCollectionName]) && count($aPropertyConfig[$sCollectionName]) > 0) {
            $this->_aCollectionConfig = $aPropertyConfig[$sCollectionName];
        }
    }

    /**
     * Check if given field name exists in config
     *
     * @param string $sField
     * @return bool
     */

    public function fieldExists($sField) {
        return in_array($sField, array_keys($this->_aCollectionConfig));
    }

    /**
     * Iterate all properties and prepares them for saving in the selected Collection
     *
     * @return array
     */

    public function getPreparedProperties() {
        $aPreparedProperties = array();

        if(!empty($this->_aCollectionConfig)) {
            // iterates collection config
            foreach($this->_aCollectionConfig as $sProperty => $aFieldConfig) {
                // get value of property or set to null, if property does not exists or is empty
                $value = (isset($this->_aProperties[$sProperty])) ? $this->_aProperties[$sProperty] : null;
                // get prepared property value
                $aPreparedProperties[$sProperty] = $this->_prepareProperty($sProperty, $value, $aFieldConfig);
            }
        }

        return $aPreparedProperties;
    }

    /**
     * Prepare a proptery for saving
     *
     * @param string $sProperty
     * @param mixed $value
     * @param array $aFieldConfig
     * @return mixed
     */

    protected function _prepareProperty($sProperty, $value, $aFieldConfig) {
         if(!empty($aFieldConfig)) {
            // set Mongo type object
            if(isset($aFieldConfig['mongoType'])) {
                $value = $this->_setMongoValueType($aFieldConfig['mongoType'], $value);
            }

            // set index
            if(isset($aFieldConfig['index']['use']) && $aFieldConfig['index']['use'] === true) {
                $this->_getCollection()->ensureIndex($sProperty);
            }

            // encrypt field data
            if(isset($aFieldConfig['encrypt'])) {
                $value = Encryption::getInstance()->encrypt($value, $this->_oConfig->getProperty('EncryptionKey'));
            }

            // set php type
            if(isset($aFieldConfig['phpType'])) {
                $value = $this->_setPhpValueType($aFieldConfig['phpType'], $value);
            }           
        }

        return $value; 
    }

    /**
     * Convert value to a Mongo type object
     *
     * @param string $sType
     * @param mixed $value
     * @return mixed
     */

    protected function _setMongoValueType($sType, $value) {
        switch($sType) {
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
     * @param string $sType
     * @param mixed $value
     * @return mixed
     */

    protected function _setPhpValueType($sType, $value) {
        switch($sType) {
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
        if(!$this->_oCollection instanceof \MongoCollection) {
            throw new \Exception('No collection selected!');
        }

        return $this->_oCollection;
    }

    /**
     * Override parent method to perpare certain values (f.e. MongoDate) to return their value
     *
     * @param string $sKey
     * @return mixed
     */

    public function getProperty($sKey) {
        $value = parent::getProperty($sKey);

        // get timestamp of MongoDate object
        if($value instanceof \MongoDate) {
            $value = date('Y-m-d H:i:s', $value->sec);
        }

        // get id of MongoId object
        if($value instanceof \MongoId) {
            $value = $value->{'$id'};
        }

        if(isset($this->_aCollectionConfig[$sKey]['encrypt'])) {
            $value = Encryption::getInstance()->decrypt($value, $this->_oConfig->getProperty('EncryptionKey'));
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
        return $this->_aProperties['_id']->{'$id'};
    }

    /**
     * Set id of current document if none exists
     */

    protected function _setId() {
        if(!isset($this->_aProperties['_id']) || !$this->_aProperties['_id'] instanceof \MongoId) {
            $this->_aProperties['_id'] = new \MongoId();
        }
    }

    /**
     * Load documend from given id
     *
     * @param string $sId
     */

    public function load($sId) {
        $aData = $this->_getCollection()->findOne(array('_id' => new \MongoId($sId)));
        
        if($aData === null) {
            throw new \Exception("Document id '{$sId}' does not exist!");
        }

        $this->_aProperties = $aData;
    }

    /**
     * Save document properties into the selected MongoDB collection
     */

    public function save() {
        $this->_setId();
        $aPreparedProperties = $this->getPreparedProperties();
        $this->_getCollection()->save($aPreparedProperties);
    }

    /**
     * Delete current document from the selected MongoDB collection
     */

    public function delete() {
        $this->_getCollection()->remove(array('_id' => $this->_aProperties['_id']));
    }
}