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

class IterateableList implements \Countable, \IteratorAggregate, \ArrayAccess {

    /**
     * Stores properties
     * @var array
     */

    protected $_properties = array();

    /**
     * Imports an array 
     *
     * @param array $properties
     */

    public function assign(array $properties) {
        $this->_properties = $properties;
    }

    /**
     * Update properties with from given array
     *
     * @param array $properties
     */

    public function updateProperties($properties) {
        if(!empty($properties)) {
            foreach($properties as $property => $value) {
                $this->setProperty($property, $value);
            }
        }
    }

    /**
     * Returns value of given property name or throws an exception if the property does not exist 
     *
     * @param string $property
     * @return mixed
     * @throws OutOfBoundsException
     */

    public function getProperty($property) {
        if(array_key_exists($property, $this->_properties)) {
            return $this->_properties[$property];
        }
        
        throw new \OutOfBoundsException("Index '{$property}' does not exist");
    }

    /**
     * Returns all properties as array
     *
     * @return array
     */

    public function getProperties() {
        // get all property names
        $properties = array_keys($this->_properties);
        $values = array();

        if(!empty($properties)) {
            foreach($properties as $property) {
                $values[$property] = $this->getProperty($property);
            }
        }

        return $values;
    }

    /**
     * Sets a property and its value
     *
     * @param string $property
     * @param mixed $value
     */

    public function setProperty($property, $value) {
        $this->_properties[$property] = $value;
    }

    /**
     * Removes a property
     *
     * @param string $property
     */

    public function removeProperty($property) {
        if(!array_key_exists($property, $this->_properties)) {
            throw new \OutOfBoundsException("Index '{$property}' does not exist");
        }

        unset($this->_properties[$property]);
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
        return count($this->_properties);
    }

    /**
     * Return ArrayIterator instance with list properties
     *
     * @return ArrayIterator
     */

    public function getIterator() {
        return new \ArrayIterator($this->_properties);
    }

    /**
     * Sets a property and its value
     *
     * @param string $property
     * @param mixed $value
     */

    public function offsetSet($property, $value) {
        $this->setProperty($property, $value);
    }

    /**
     * Checks if given property name exists
     *
     * @param string $property
     * @return bool
     */
    
    public function offsetExists($property) {
        return isset($this->_properties[$property]);
    }

    /**
     * Removes a property
     *
     * @param string $property
     */
    
    public function offsetUnset($property) {
        $this->removeProperty($property);
    }

    /**
     * Returns value of given property name
     *
     * @param string $property
     * @return mixed
     */   

    public function offsetGet($property) {
        return $this->getProperty($property);
    }
    
}