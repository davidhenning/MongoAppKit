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

}