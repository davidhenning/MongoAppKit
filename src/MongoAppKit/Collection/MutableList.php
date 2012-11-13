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

namespace MongoAppKit\Collection;

class MutableList implements \Countable, \IteratorAggregate {

    /**
     * Stores properties
     * @var array
     */

    protected $_properties = array();

    /**
     * Constructor
     *
     * Accepts any arguments and saves them as properties
     *
     * @param mixed [...]
     */

    public function __construct() {
        $args = func_get_args();

        if(!empty($args)) {
            $this->_properties = $args;
        }
    }

    public function __get($property) {
        if($property === 'length') {
            return $this->count();
        }

        return $this->getProperty($property);
    }

    public function __set($property, $value) {
        $this->setProperty($property, $value);
    }

    public function __isset($property) {
        return isset($this->_properties[$property]);
    }

    public function __unset($property) {
        $this->removeProperty($property);
    }

    public function __toString() {
        return serialize($this->_properties);
    }

    public function head() {
        return reset($this->_properties);
    }

    public function last() {
        return end($this->_properties);
    }

    public function reverse() {
        $this->_properties = array_reverse($this->_properties);

        return $this;
    }

    public function map($callback) {
        if(!is_callable($callback)) {
            throw new \InvalidArgumentException('Mapping function is not callable!');
        }

        $this->_properties = array_map($callback, $this->_properties);

        return $this;
    }

    public function slice($offset, $limit) {
        $properties = array_slice($this->_properties, $offset, $limit);
        $list = new self();
        $list->assign($properties);

        return $list;
    }

    public function filter($callback) {
        if(!is_callable($callback)) {
            throw new \InvalidArgumentException('Filter is not callable!');
        }

        $properties = array_filter($this->_properties, $callback);
        $list = new self();
        $list->assign($properties);

        return $list;
    }

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

    /**
     * Returns value of given property name or throws an exception if the property does not exist
     *
     * @param string $property
     * @return mixed
     * @throws \OutOfBoundsException
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
     * @return \ArrayIterator
     */

    public function getIterator() {
        return new \ArrayIterator($this->_properties);
    }
}