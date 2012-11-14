<?php

namespace MongoAppKit\Tests\Document;

use MongoAppKit\Config,
    MongoAppKit\Application,
    MongoAppKit\Document\Document as MongoAppKitDocument;

class DocumentTest extends \PHPUnit_Framework_TestCase
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
        $config->setProperty('BaseDir', '/');
        $config->setProperty('DebugMode', true);
        $config->setProperty('EncryptionKey', 'muh');
        $fields = array(
            'test' => array(
                '_id' => array('mongoType' => 'id'),
                'created_at' => array('mongoType' => 'date', 'index' => true),
                'foo' => array(),
                'bar' => array('encrypt' => true),
                'typeInt' => array('phpType' => 'int'),
                'typeFloat' => array('phpType' => 'float'),
                'typeBool' => array('phpType' => 'bool'),
                'typeString' => array('phpType' => 'string')
            )
        );

        $config->setProperty('Fields', $fields);

        $this->_app = new Application($config);
        ;
    }

    public function tearDown()
    {
        $this->_app['storage']->getDatabase()->test->drop();
    }

    public function testDocument()
    {
        $app = $this->_app;

        $expectedDocument = new MongoAppKitDocument($app, 'test');
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->save();
        $id = $expectedDocument->getProperty('_id');

        $document = new MongoAppKitDocument($app, 'test');
        $document->load($id);

        $this->assertEquals($expectedDocument, $document);
    }

    public function testGetDatabase()
    {
        $app = $this->_app;

        $document = new MongoAppKitDocument($app, 'test');

        $this->assertTrue($document->getDatabase() instanceof \MongoDB);
    }

    public function testEmptyCollectionName()
    {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $document = new MongoAppKitDocument($app, null);
            $document->findAll();
        } catch (\InvalidArgumentException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testFieldEncryption()
    {
        $app = $this->_app;

        $expectedDocument = new MongoAppKitDocument($app, 'test');
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->save();
        $id = $expectedDocument->getProperty('_id');

        $document = new MongoAppKitDocument($app, 'test');
        $document->load($id);

        $this->assertEquals('foo', $document->getProperty('bar'));
    }

    public function testFieldTypeDate()
    {
        $app = $this->_app;

        $time = time();
        $formattedTime = date('Y-m-d H:i:s', $time);

        $expectedDocument = new MongoAppKitDocument($app, 'test');
        $expectedDocument->setProperty('created_at', $formattedTime);
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->save();
        $id = $expectedDocument->getProperty('_id');

        $document = new MongoAppKitDocument($app, 'test');
        $document->load($id);

        $this->assertEquals($time, $document->getProperty('created_at'));
    }

    public function testDocumentGetId()
    {
        $app = $this->_app;

        $expectedDocument = new MongoAppKitDocument($app, 'test');
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->save();
        $id = $expectedDocument->getId();

        $document = new MongoAppKitDocument($app, 'test');
        $document->load($id);

        $this->assertEquals($expectedDocument, $document);
    }

    public function testLoadWithInvalidId()
    {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $document = new MongoAppKitDocument($app, null);
            $document->load('dfdsfdsffsdf');
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testDocumentPhpTypeInt()
    {
        $app = $this->_app;

        $document = new MongoAppKitDocument($app, 'test');
        $document->setProperty('typeInt', '5');
        $document->save();

        $this->assertEquals($document->getProperty('typeInt'), 5);
    }

    public function testDocumentPhpTypeFloat()
    {
        $app = $this->_app;

        $document = new MongoAppKitDocument($app, 'test');
        $document->setProperty('typeFloat', '5.05');
        $document->save();

        $this->assertEquals($document->getProperty('typeFloat'), 5.05);
    }

    public function testDocumentPhpTypeBool()
    {
        $app = $this->_app;

        $document = new MongoAppKitDocument($app, 'test');
        $document->setProperty('typeBool', 'true');
        $document->save();

        $this->assertEquals($document->getProperty('typeBool'), true);
    }

    public function testDocumentPhpTypeString()
    {
        $app = $this->_app;

        $document = new MongoAppKitDocument($app, 'test');
        $document->setProperty('typeString', 'string');
        $document->save();

        $this->assertEquals($document->getProperty('typeString'), 'string');
    }

    public function testDelete()
    {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $document = new MongoAppKitDocument($app, null);
            $document->save();
            $id = $document->getId();
            $document->remove();
            $document->load($id);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }
}
