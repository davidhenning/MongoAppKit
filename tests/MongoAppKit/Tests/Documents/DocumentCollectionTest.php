<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Config,
    MongoAppKit\Application,
    MongoAppKit\Documents\Document,
    MongoAppKit\Documents\DocumentCollection;

class DocumentCollectionTest extends \PHPUnit_Framework_TestCase {

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
                'foo' => array(),
                'sort' => array()
            )
        );

        $config->setProperty('Fields', $fields);
        $app = new Application($config);
        $this->_app = $app;

        for($i = 0; $i < 10; $i++) {
            $document = new Document($app, 'test');
            $document->setProperty('foo', 'bar');
            $document->setProperty('sort', 10 - $i);
            $document->save();
        }

    }

    public function tearDown() {
        $this->_app['storage']->getDatabase()->test->drop();
    }

    public function testFindAll() {
        $app = $this->_app;
        $collection = new DocumentCollection(new Document($app, 'test'));
        $collection->findAll();

        $this->assertEquals(10, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testFind() {
        $app = $this->_app;
        $collection = new DocumentCollection(new Document($app, 'test'));
        $collection->find(100, 0, array('sort' => 1));

        $this->assertEquals(1, $collection->getFoundDocuments());
        $this->assertEquals(1, $collection->getTotalDocuments());
    }

    public function testFindSkip() {
        $app = $this->_app;
        $collection = new DocumentCollection(new Document($app, 'test'));
        $collection->find(100, 9, array('foo' => 'bar'));

        $this->assertEquals(1, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testSortByAsc() {
        $app = $this->_app;
        $collection = new DocumentCollection(new Document($app, 'test'));
        $collection->sortBy('sort', 'asc');
        $collection->findAll();

        $i = 1;

        foreach($collection as $document) {
            $this->assertEquals($i, $document->getProperty('sort'));
            $i++;
        }

        $this->assertEquals(10, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testSortByDesc() {
        $app = $this->_app;
        $collection = new DocumentCollection(new Document($app, 'test'));
        $collection->sortBy('sort', 'desc');
        $collection->findAll();

        $i = 10;

        foreach($collection as $document) {
            $this->assertEquals($i, $document->getProperty('sort'));
            $i--;
        }

        $this->assertEquals(10, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }
    public function testSortByNull() {
        $app = $this->_app;
        $collection = new DocumentCollection(new Document($app, 'test'));
        $collection->sortBy('sort', null);
        $collection->findAll();

        $i = 1;

        foreach($collection as $document) {
            $this->assertEquals($i, $document->getProperty('sort'));
            $i++;
        }

        $this->assertEquals(10, $collection->getFoundDocuments());
        $this->assertEquals(10, $collection->getTotalDocuments());
    }

    public function testSortByInvalidDirection() {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $collection = new DocumentCollection(new Document($app, 'test'));
            $collection->sortBy('sort', 'fgdgdg');
            $collection->findAll();
        } catch(\InvalidArgumentException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testSortByInvalidField() {
        $app = $this->_app;
        $exceptionThrown = false;

        try {
            $collection = new DocumentCollection(new Document($app, 'test'));
            $collection->sortBy('dfsafsfsf', 'fgdgdg');
            $collection->findAll();
        } catch(\InvalidArgumentException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }
}
