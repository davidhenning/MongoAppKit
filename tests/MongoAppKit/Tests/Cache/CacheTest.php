<?php

namespace MongoAppKit\Tests\Cache;

use MongoAppKit\Application,
    MongoAppKit\Config,
    MongoAppKit\Cache\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $config = new Config();
        $config->setProperty('MongoServer', 'localhost');
        $config->setProperty('MongoPort', 27017);
        $config->setProperty('MongoUser', null);
        $config->setProperty('MongoPassword', null);
        $config->setProperty('MongoDatabase', 'phpunit');
        $config->setProperty('AppName', 'testcase');
        $config->setProperty('BaseDir', realpath(__DIR__  . '/../../../../'));
        $config->setProperty('DebugMode', true);

        $this->_app = new Application($config);
    }

    public function testStore()
    {
        $app = $this->_app;
        $cache = new Cache($app, array());
        $value = array('foo' => 'bar');
        $cache->store('test', $value);

        $this->assertEquals($value, $cache->retrieve('test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */

    public function testRetrievalFail() {
        $app = $this->_app;
        $cache = new Cache($app, array());
        $cache->retrieve('foo');
    }
}
