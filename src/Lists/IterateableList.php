<?php

/**
 * Class IterateableList
 *
 * Implements the SPL interfaces Countable, Iterator and ArrayAccess to emulate the full capabilities of an PHP array
 * 
 * @author David Henning <madcat.me@gmail.com>
 * 
 * @package MongoAppKit
 */

namespace MongoAppKit\Lists;

use MongoAppKit\Base;

class IterateableList extends Base implements \Countable, \IteratorAggregate, \ArrayAccess {

    /**
     * Stores properties
     * @var array
     */

    protected $_aProperties = array();

    /**
     * Imports an array 
     *
     * @param array $aProperties
     */

    public function assign(array $aProperties) {
        $this->_aProperties = $aProperties;
    }

    /**
     * Update properties with from given array
     *
     * @param array $aProperties
     */

    public function updateProperties($aProperties) {
        if(!empty($aProperties)) {
            foreach($aProperties as $sProperty => $value) {
                $this->setProperty($sProperty, $value);
            }
        }
    }

    /**
     * Returns value of given property name or throws an exception if the property does not exist 
     *
     * @param string $sKey
     * @return mixed
     * @throws OutOfBoundsException
     */

    public function getProperty($sKey) {
        if(array_key_exists($sKey, $this->_aProperties)) {
            return $this->_aProperties[$sKey];
        }
        
        throw new \OutOfBoundsException("Index '{$sKey}' does not exist");
    }

    /**
     * Returns all properties as array
     *
     * @return array
     */

    public function getProperties() {
        // get all property names
        $aProperties = array_keys($this->_aProperties);
        $aValues = array();

        if(!empty($aProperties)) {
            foreach($aProperties as $sProperty) {
                $aValues[$sProperty] = $this->getProperty($sProperty);
            }
        }

        return $aValues;
    }

    /**
     * Sets a property and its value
     *
     * @param string $sKey
     * @param mixed $value
     */

    public function setProperty($sKey, $value) {
        $this->_aProperties[$sKey] = $value;
    }

    /**
     * Removes a property
     *
     * @param string $sKey
     */

    public function removeProperty($sKey) {
        if(!array_key_exists($sKey, $this->_aProperties)) {
            throw new \OutOfBoundsException("Index '{$sKey}' does not exist");
        }

        unset($this->_aProperties[$sKey]);
    }

    /******************/
    /* SPL interfaces */
    /******************/

    /**
     * Returns count of object properties
     *
     * @return int
     */

    public function count() {
        return count($this->_aProperties);
    }

    /**
     * Return ArrayIterator instance with list properties
     *
     * @return ArrayIterator
     */

    public function getIterator() {
        return new \ArrayIterator($this->_aProperties);
    }

    /**
     * Sets a property and its value
     *
     * @param string $sKey
     * @param mixed $value
     */

    public function offsetSet($sKey, $value) {
        $this->setProperty($sKey, $value);
    }

    /**
     * Checks if given property name exists
     *
     * @param string $sKey
     * @return bool
     */
    
    public function offsetExists($sKey) {
        return isset($this->_aProperties[$sKey]);
    }

    /**
     * Removes a property
     *
     * @param string $sKey
     */
    
    public function offsetUnset($sKey) {
        $this->removeProperty($sKey);
    }

    /**
     * Returns value of given property name
     *
     * @param string $sKey
     * @return mixed
     */   

    public function offsetGet($sKey) {
        return $this->getProperty($sKey);
    }
    
}