<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Config,
	MongoAppKit\Storage,
	MongoAppKit\View;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class ViewTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		$config = new Config();
		$config->setProperty('MongoServer', 'localhost');
		$config->setProperty('MongoPort', 27017);
		$config->setProperty('MongoUser', null);
		$config->setProperty('MongoPassword', null);
		$config->setProperty('MongoDatabase', 'phpunit');
		$config->setProperty('AppName', 'testcase');
		
		$storage = new Storage($config);
		
		$app = new Application();
		$app['config'] = $config;
		$app['storage'] = $storage;

		$this->_app = $app;
	}

	public function testSetId() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = '123456';

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);		
			$view->setId($expectedValue);

			return $view->getId();
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}

	public function testSetOutputFormat() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = 'json';

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);		
			$view->setOutputFormat($expectedValue);

			return $view->getOutputFormat();
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}

	public function testSetInvalidOutputFormat() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = 'xhtml';

		
		$app->get('/', function() use ($app, $expectedValue) {
			try {
				$view = new View($app);		
				$view->setOutputFormat($expectedValue);

				return 'true';
			} catch(\InvalidArgumentException $e) {
				return '';
			}

		});

		$this->assertEquals('', $app->handle($request)->getContent());
	}

	public function testSetTotalDocuments() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = 20;

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);		
			$view->setTotalDocuments($expectedValue);

			return $view->getTotalDocuments();
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}

	public function testSetDocumentLimit() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = 10;

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);		
			$view->setDocumentLimit($expectedValue);

			return $view->getDocumentLimit();
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}	
}