<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Application,
    MongoAppKit\Config,
	MongoAppKit\Storage,
	MongoAppKit\View;

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
        $config->setProperty('BaseDir', '/');
        $config->setProperty('DebugMode', true);

		$this->_app = new Application($config);;
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

	public function testSetAppName() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = 'MongoAppKit';

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);		
			$view->setAppName($expectedValue);

			return $view->getAppName();
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}
	
	public function testSetSkippedDocuments() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = 10;

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);		
			$view->setSkippedDocuments($expectedValue);

			return $view->getSkippedDocuments();
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}

	public function testGutCurrentPage() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = 2;

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);	

			$view->setSkippedDocuments(101);
			$view->setDocumentLimit(100);

			return $view->getCurrentPage();
		});

		$this->assertEquals($expectedValue, (int)$app->handle($request)->getContent());
	}

	public function testAddAdditionalUrlParameter() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = serialize(array('foo' => 'bar'));

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);		
			$view->addAdditionalUrlParameter('foo', 'bar');

			return serialize($view->getAdditionalUrlParameters());
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}

	public function testGetPages() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = serialize(array(
            'pages' => array(
            	array(
                    'nr' => 1,
                    'url' => '.html?skip=0&limit=100',
                    'active' => true
            	),
            	array(
                    'nr' => 2,
                    'url' => '.html?skip=100&limit=100',
                    'active' => false
            	)            	
            ),
            'currentPage' => 1,
            'documentsPerPage' => 100,
            'totalPages' => 2,
            'prevPageUrl' => false,
            'firstPageUrl' => false,
            'nextPageUrl' => '.html?skip=100&limit=100',
            'lastPageUrl' => '.html?skip=100&limit=100'
		));

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);
			$view->setDocumentLimit(100);
			$view->setTotalDocuments(101);	

			return serialize($view->getPagination());
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}

	public function testGetPagesOnPage2() {
		$app = $this->_app;
		$request = Request::create('/');
		$expectedValue = serialize(array(
            'pages' => array(
            	array(
                    'nr' => 1,
                    'url' => '.html?skip=0&limit=100',
                    'active' => false
            	),
            	array(
                    'nr' => 2,
                    'url' => '.html?skip=100&limit=100',
                    'active' => true
            	)            	
            ),
            'currentPage' => 2,
            'documentsPerPage' => 100,
            'totalPages' => 2,
            'prevPageUrl' => '.html?skip=0&limit=100',
            'firstPageUrl' => '.html?skip=0&limit=100',
            'nextPageUrl' => false,
            'lastPageUrl' => false
		));

		$app->get('/', function() use ($app, $expectedValue) {
			$view = new View($app);
			$view->setDocumentLimit(100);
			$view->setSkippedDocuments(100);
			$view->setTotalDocuments(101);	

			return serialize($view->getPagination());
		});

		$this->assertEquals($expectedValue, $app->handle($request)->getContent());
	}
}