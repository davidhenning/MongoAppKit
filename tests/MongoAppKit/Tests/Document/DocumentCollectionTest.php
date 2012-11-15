<?php

namespace MongoAppKit\Tests\Document;

use MongoAppKit\Config,
    MongoAppKit\Application,
    MongoAppKit\Document\Document as MongoAppKitDocument,
    MongoAppKit\Document\DocumentCollection;

class DocumentCollectionTest extends \PHPUnit_Framework_TestCase
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
        $fields = array(
            'test' => array(
                '_id' => array('mongoType' => 'id'),
                'foo' => array(),
                'sort' => array()
            )
        );

        $config->setProperty('Fields', $fields);
        $app = new Application($config);
        $this->_app = $app;

        for ($i = 0; $i < 10; $i++) {
            $document = new MongoAppKitDocument($app, 'test');
            $document->setProperty('foo', 'bar');
            $document->setProperty('sort', 10 - $i);
            $document->store();
        }
    }

    public function tearDown()
    {
        $this->_app['storage']->getDatabase()->test->drop();
    }

    public function testFindAll()
    {
        $app = $this->_app;
        $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
        $collection->find();

        $this->assertEquals(10, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testFind()
    {
        $app = $this->_app;
        $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
        $collection->find(array('sort' => 1));

        $this->assertEquals(1, $collection->getFoundDocuments());
        $this->assertEquals(1, $collection->getTotalDocuments());
    }

    public function testFindSkip()
    {
        $app = $this->_app;
        $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
        $collection->find(array('foo' => 'bar'), null, 9);

        $this->assertEquals(1, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testSortByAsc()
    {
        $app = $this->_app;
        $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
        $collection->sortBy('sort', 'asc');
        $collection->find();

        $i = 1;

        foreach ($collection as $document) {
            $this->assertEquals($i, $document->getProperty('sort'));
            $i++;
        }

        $this->assertEquals(10, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testSortByDesc()
    {
        $app = $this->_app;
        $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
        $collection->sortBy('sort', 'desc');
        $collection->find();

        $i = 10;

        foreach ($collection as $document) {
            $this->assertEquals($i, $document->getProperty('sort'));
            $i--;
        }

        $this->assertEquals(10, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testSortByNull()
    {
        $app = $this->_app;
        $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
        $collection->sortBy('sort', null);
        $collection->find();

        $i = 1;

        foreach ($collection as $document) {
            $this->assertEquals($i, $document->getProperty('sort'));
            $i++;
        }

        $this->assertEquals(10, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testSortByInvalidDirection()
    {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
            $collection->sortBy('sort', 'fgdgdg');
            $collection->find();
        } catch (\InvalidArgumentException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testSortByInvalidField()
    {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
            $collection->sortBy('dfsafsfsf', 'fgdgdg');
            $collection->find();
        } catch (\InvalidArgumentException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testRemove()
    {
        $app = $this->_app;

        for ($i = 0; $i < 10; $i++) {
            $document = new MongoAppKitDocument($app, 'test');
            $document->setProperty('foo', 'removeTest');
            $document->store();
        }

        $collection = new DocumentCollection(new MongoAppKitDocument($app, 'test'));
        $collection->find(array('foo' => 'removeTest'));
        $collection->foo = 'bar';
        $this->assertEquals(11, $collection->length);

        $collection->remove();

        $this->assertEquals(1, $collection->length);
        $this->assertEquals('bar', $collection->foo);
    }
}
