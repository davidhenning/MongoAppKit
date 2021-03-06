<?php

namespace MongoAppKit\Tests\Document;

use MongoAppKit\Config,
    MongoAppKit\Application,
    MongoAppKit\Document\Document as MongoAppKitDocument;

use Collection\MutableMap;

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

        $this->_app = new Application($config);
    }

    public function getDocument(Application $app)
    {
        $document = new MongoAppKitDocument($app, 'test');
        $document->setFields(array(
            '_id' => array('mongoType' => 'id'),
            'created_at' => array('mongoType' => 'date', 'index' => true),
            'foo' => array(),
            'bar' => array('encrypt' => true),
            'typeInt' => array('phpType' => 'int'),
            'typeFloat' => array('phpType' => 'float'),
            'typeBool' => array('phpType' => 'bool'),
            'typeString' => array('phpType' => 'string')
        ));

        return $document;
    }

    public function tearDown()
    {
        $this->_app['storage']->getDatabase()->test->drop();
    }

    public function testDocument()
    {
        $app = $this->_app;

        $expectedDocument = $this->getDocument($app);
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->store();
        $id = $expectedDocument->getProperty('_id');

        $document = $this->getDocument($app);
        $document->load($id);

        $this->assertEquals($expectedDocument, $document);
    }

    public function testDocumentArrayToMapValue()
    {
        $app = $this->_app;

        $list = new MutableMap('foo', 'bar');
        $document = $this->getDocument($app);
        $document->setProperty('foo', array('foo', 'bar'));

        $this->assertEquals($list, $document->getProperty('foo'));
    }

    public function testGetDatabase()
    {
        $app = $this->_app;

        $document = $this->getDocument($app);

        $this->assertTrue($document->getDatabase() instanceof \MongoDB);
    }

    /**
     * @expectedException \InvalidArgumentException
     */

    public function testEmptyCollectionName()
    {
        $app = $this->_app;
        $document = new MongoAppKitDocument($app, null);
    }

    public function testFieldEncryption()
    {
        $app = $this->_app;

        $expectedDocument = $this->getDocument($app);
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->store();
        $id = $expectedDocument->getProperty('_id');

        $document = $this->getDocument($app);
        $document->load($id);

        $this->assertEquals('foo', $document->getProperty('bar'));
    }

    public function testFieldTypeDate()
    {
        $app = $this->_app;

        $time = time();
        $formattedTime = date('Y-m-d H:i:s', $time);

        $expectedDocument = $this->getDocument($app);
        $expectedDocument->setProperty('created_at', $formattedTime);
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->store();
        $id = $expectedDocument->getProperty('_id');

        $document = $this->getDocument($app);
        $document->load($id);

        $this->assertEquals($time, $document->getProperty('created_at'));
    }

    public function testDocumentGetId()
    {
        $app = $this->_app;

        $expectedDocument = $this->getDocument($app);
        $expectedDocument->setProperty('foo', 'bar');
        $expectedDocument->setProperty('bar', 'foo');
        $expectedDocument->store();
        $id = $expectedDocument->getId();

        $document = $this->getDocument($app);
        $document->load($id);

        $this->assertEquals($expectedDocument, $document);
    }

    /**
     * @expectedException \InvalidArgumentException
     */

    public function testLoadWithInvalidId()
    {
        $app = $this->_app;
        $document = new MongoAppKitDocument($app, null);
        $document->load('dfdsfdsffsdf');
    }

    public function testDocumentPhpTypeInt()
    {
        $app = $this->_app;

        $document = $this->getDocument($app);
        $document->setProperty('typeInt', '5');
        $document->store();

        $this->assertEquals($document->getProperty('typeInt'), 5);
    }

    public function testDocumentPhpTypeFloat()
    {
        $app = $this->_app;

        $document = $this->getDocument($app);
        $document->setProperty('typeFloat', '5.05');
        $document->store();

        $this->assertEquals($document->getProperty('typeFloat'), 5.05);
    }

    public function testDocumentPhpTypeBool()
    {
        $app = $this->_app;

        $document = $this->getDocument($app);
        $document->setProperty('typeBool', 'true');
        $document->store();

        $this->assertEquals($document->getProperty('typeBool'), true);
    }

    public function testDocumentPhpTypeString()
    {
        $app = $this->_app;

        $document = $this->getDocument($app);
        $document->setProperty('typeString', 'string');
        $document->store();

        $this->assertEquals($document->getProperty('typeString'), 'string');
    }

    /**
     * @expectedException \InvalidArgumentException
     */

    public function testDelete()
    {
        $app = $this->_app;
        $document = new MongoAppKitDocument($app, null);
        $document->store();
        $id = $document->getId();
        $document->remove();
        $document->load($id);
    }
}
