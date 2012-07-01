<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Config,
	MongoAppKit\Storage;

class StorageTest extends \PHPUnit_Framework_TestCase {

	public function testStorage() {
		$config = new Config();
		$config->setProperty('MongoServer', 'localhost');
		$config->setProperty('MongoPort', 27017);
		$config->setProperty('MongoUser', null);
		$config->setProperty('MongoPassword', null);
		$config->setProperty('MongoDatabase', 'phpunit');
		$storage = new Storage($config);

		$this->assertTrue($storage->getDatabase() instanceof \MongoDB);
	}
}