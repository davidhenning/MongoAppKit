<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Config,
    MongoAppKit\Application,
    MongoAppKit\Documents\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        $config = new Config();
        $config->setProperty('MongoServer', 'localhost');
        $config->setProperty('MongoPort', 27017);
        $config->setProperty('MongoUser', null);
        $config->setProperty('MongoPassword', null);
        $config->setProperty('MongoDatabase', 'phpunit');
        $config->setProperty('AppName', 'testcase');
        $config->setProperty('BaseDir', '/');
        $config->setProperty('DebugMode', true);
        $fields = array(
            'test' => array(
                '_id' => array('mongoType' => 'id'),
                'foo' => array()
            )
        );

        $config->setProperty('Fields', $fields);

        $this->_app = new Application($config);;
    }

    public function tearDown() {
        $this->_app['storage']->getDatabase()->drop();
    }

    public function testDocument() {
        $app = $this->_app;

        $expectedDocument = new Document($app, 'test');
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->save();
        $id = $expectedDocument->getId();

        $document = new Document($app, 'test');
        $document->load($id);

        $this->assertEquals($expectedDocument, $document);
    }

}
