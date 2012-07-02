<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase {

	public function testSanitizeTrim() {
		$config = new Config();
		$value = ' value ';
		$expectedValue = trim($value);
		$sanitizedValue = $config->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

	public function testSanitizeUrlDecode() {
		$config = new Config();
		$value = rawurlencode('value [] =');
		$expectedValue = rawurldecode($value);
		$sanitizedValue = $config->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

	public function testSanitizeSpecialChars() {
		$config = new Config();
		$value = '<b>strong</b>';
		$expectedValue = htmlspecialchars($value);
		$sanitizedValue = $config->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

	public function testSanitizeArray() {
		$config = new Config();
		$value = array('foo' => ' bar ');
		$expectedValue = array('foo' => 'bar');
		$sanitizedValue = $config->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

	public function testSanitizeNull() {
		$config = new Config();
		$value = null;
		$expectedValue = null;
		$sanitizedValue = $config->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

	public function testAddConfigFile() {
		$values = array('foo' => 'bar');
		$fileName = __DIR__ . '/test.json';
		file_put_contents($fileName, json_encode($values));

		$config = new Config();
		$config->addConfigFile($fileName);
		unlink($fileName);

		$this->assertEquals($values, $config->getProperties());
	}

	public function testAddConfigFileWithEmptyFileName() {
		try {
			$config = new Config();
			$config->addConfigFile(null);
		} catch(\InvalidArgumentException $e) {
			return;
		}

		$this->fail('Expected InvalidArgumentException was not thrown.');
	}

	public function testAddConfigFileWithWrongFileName() {
		try {
			$config = new Config();
			$config->addConfigFile('dfgkhdufhdjhfjk');
		} catch(\InvalidArgumentException $e) {
			return;
		}

		$this->fail('Expected InvalidArgumentException was not thrown.');
	}
}