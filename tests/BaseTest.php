<?php

namespace MongoAppKit;

class BaseTest extends \PHPUnit_Framework_TestCase {

	public function testGetConfig() {
		$oBase = new Base();
		$this->assertTrue($oBase->getConfig() instanceof Config);
	}

	public function testGetStorage() {
		$oBase = new Base();
		$this->assertTrue($oBase->getStorage() instanceof Storage);
	}
}