<?php

namespace MongoAppKit\Cache;

use Silex\Application;

use MongoAppKit\Cache\Method\MethodInterface;

class Cache
{
    protected $_method;

    public function __construct(Application $app, $options = array())
    {
        $config = $app['config'];
        $options = array_merge(array(
            'ttl' => 1800,
            'methodClass' => '\MongoAppKit\Cache\Method\FileSystem',
            'cacheDir' => $config->getBaseDir() . '/tmp/'
        ), $options);

        $this->_ttl = $options['ttl'];
        $this->_setMethod($options['methodClass'], $options);
    }

    public function store($name, $value)
    {
        if (!is_array($value) && !is_object($value)) {#
            throw new \InvalidArgumentException("Item '{$name} is no array or object and cannot be stored in cache!");
        }

        $value = serialize($value);

        $this->_getMethod()->store($name, $value);
    }

    public function retrieve($name)
    {
        $value = $this->_getMethod()->retrieve($name);

        return unserialize($value);
    }

    protected function _getMethod()
    {
        return $this->_method;
    }

    protected function _setMethod($class, array $options)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Class '{$class}' does not exist!'");
        }

        $method = new $class();

        if (!$method instanceof MethodInterface) {
            throw new \InvalidArgumentException("Class '{$class}' does not implement MethodInterface!'");
        }

        $method->setOptions($options);
        $method->cleanUp();

        $this->_method = $method;
    }
}
