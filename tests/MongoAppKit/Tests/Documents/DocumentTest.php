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
        $config->setProperty('EncryptionKey', 'muh');
        $fields = array(
            'test' => array(
                '_id' => array('mongoType' => 'id'),
                'created_at' => array('mongoType' => 'date'),
                'foo' => array(),
                'bar' => array('encrypt' => true)
            )
        );

        $config->setProperty('Fields', $fields);

        $this->_app = new Application($config);;
    }

    public function tearDown() {
        $this->_app['storage']->getDatabase()->test->drop();
    }

    public function testDocument() {
        $app = $this->_app;

        $expectedDocument = new Document($app, 'test');
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->save();
        $id = $expectedDocument->getProperty('_id');

        $document = new Document($app, 'test');
        $document->load($id);

        $this->assertEquals($expectedDocument, $document);
    }

    public function testGetDatabase() {
        $app = $this->_app;

        $document = new Document($app, 'test');

        $this->assertTrue($document->getDatabase() instanceof \MongoDB);
    }

    public function testEmptyCollectionName() {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $document = new Document($app, null);
            $document->findAll();
        } catch(\InvalidArgumentException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testFieldEncryption() {
        $app = $this->_app;

        $expectedDocument = new Document($app, 'test');
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->save();
        $id = $expectedDocument->getProperty('_id');

        $document = new Document($app, 'test');
        $document->load($id);

        $this->assertEquals('foo', $document->getProperty('bar'));
    }

    public function testFieldTypeDate() {
        $app = $this->_app;

        $formattedTime = date('Y-m-d H:i:s');

        $expectedDocument = new Document($app, 'test');
        $expectedDocument->setProperty('created_at', $formattedTime);
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->save();
        $id = $expectedDocument->getProperty('_id');

        $document = new Document($app, 'test');
        $document->load($id);

        $this->assertEquals($formattedTime, $document->getProperty('created_at'));
    }

    public function testDocumentGetId() {
        $app = $this->_app;

        $expectedDocument = new Document($app, 'test');
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->save();
        $id = $expectedDocument->getId();

        $document = new Document($app, 'test');
        $document->load($id);

        $this->assertEquals($expectedDocument, $document);
    }

    public function testLoadWithInvalidId() {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $document = new Document($app, null);
            $document->load('dfdsfdsffsdf');
        } catch(\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

}
