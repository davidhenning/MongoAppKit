<?php

namespace MongoAppKit\Collection;

class MutableList implements \Countable, \IteratorAggregate
{

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

    public function __construct()
    {
        $args = func_get_args();

        if (!empty($args)) {
            $this->_properties = $args;
        }
    }

    /**
     * Magic method __get()
     *
     * Gets a property from property array
     *
     * @param string $property
     * @return mixed
     */

    public function __get($property)
    {
        if ($property === 'length') {
            return $this->count();
        }

        return $this->getProperty($property);
    }

    /**
     * Magic method __set()
     *
     * Sets a property to property array
     *
     * @param string $property property name
     * @param mixed $value property value
     */

    public function __set($property, $value)
    {
        $this->setProperty($property, $value);
    }

    /**
     * Magic method __isset()
     *
     * Checks if a property exists in property array
     *
     * @param string $property
     * @return bool
     */

    public function __isset($property)
    {
        return isset($this->_properties[$property]);
    }

    /**
     * Magic method __unset()
     *
     * Removes a property from property array
     *
     * @param string $property
     */

    public function __unset($property)
    {
        $this->removeProperty($property);
    }

    /**
     * Magic method __toString
     *
     * Serializes property array and returns the string
     *
     * @return string
     */

    public function __toString()
    {
        return serialize($this->_properties);
    }

    /**
     * Returns the first item of property array
     *
     * @return mixed
     */

    public function head()
    {
        return reset($this->_properties);
    }

    /**
     * Returns the last item of property array
     *
     * @return mixed
     */

    public function last()
    {
        return end($this->_properties);
    }

    /**
     * Reverses the property array
     *
     * @return MutableList
     */

    public function reverse()
    {
        $this->_properties = array_reverse($this->_properties);

        return $this;
    }

    /**
     * Executes given callback function on every property array item
     *
     * @param callable $callback
     * @return MutableList
     * @throws \InvalidArgumentException
     */

    public function each($callback)
    {
        return $this->map($callback);
    }

    /**
     * Executes given mapping function on every property array item
     *
     * @param callable $callback
     * @return MutableList
     * @throws \InvalidArgumentException
     */

    public function map($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Mapping function is not callable!');
        }

        $this->_properties = array_map($callback, $this->_properties);

        return $this;
    }

    /**
     * Returns new MutableList with sliced property array
     *
     * @param int $offset
     * @param int $limit
     * @return MutableList
     */

    public function slice($offset, $limit)
    {
        $properties = array_slice($this->_properties, $offset, $limit);
        $list = new self();
        $list->assign($properties);

        return $list;
    }

    /**
     * Returns new MutableList with filtered values from property array
     *
     * @param callable $callback
     * @return MutableList
     * @throws \InvalidArgumentException
     */

    public function filter($callback)
    {
        if (!is_callable($callback)) {
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
     * @return MutableList
     */

    public function assign(array $properties)
    {
        $this->_properties = $properties;

        return $this;
    }

    /**
     * Update properties with from given array
     *
     * @param array $properties
     * @return MutableList
     */

    public function updateProperties($properties)
    {
        if (!empty($properties)) {
            foreach ($properties as $property => $value) {
                $this->setProperty($property, $value);
            }
        }

        return $this;
    }

    /**
     * Returns value of given property name or throws an exception if the property does not exist
     *
     * @param string $property
     * @return mixed
     * @throws \OutOfBoundsException
     */

    public function getProperty($property)
    {
        if (array_key_exists($property, $this->_properties)) {
            return $this->_properties[$property];
        }

        throw new \OutOfBoundsException("Index '{$property}' does not exist");
    }

    /**
     * Sets a property and its value
     *
     * @param string $property
     * @param mixed $value
     * @return MutableList
     */

    public function setProperty($property, $value)
    {
        $this->_properties[$property] = $value;

        return $this;
    }

    /**
     * Removes a property
     *
     * @param string $property
     * @return MutableList
     */

    public function removeProperty($property)
    {
        if (!array_key_exists($property, $this->_properties)) {
            throw new \OutOfBoundsException("Index '{$property}' does not exist");
        }

        unset($this->_properties[$property]);

        return $this;
    }

    /**
     * Returns all properties as new MutableList
     *
     * @return array
     */

    public function getProperties()
    {
        // get all property names
        $properties = array_keys($this->_properties);
        $values = array();

        if (!empty($properties)) {
            foreach ($properties as $property) {
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

    public function count()
    {
        return count($this->_properties);
    }

    /**
     * Return ArrayIterator instance with list properties
     *
     * @return \ArrayIterator
     */

    public function getIterator()
    {
        return new \ArrayIterator($this->_properties);
    }
}