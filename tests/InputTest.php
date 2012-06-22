<?php

namespace MongoAppKit;

class InputTest extends \PHPUnit_Framework_TestCase {

	public function testSanitizeTrim() {
		$input = Input::getInstance();
		$value = ' value ';
		$expectedValue = trim($value);
		$sanitizedValue = $input->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

	public function testSanitizeUrlDecode() {
		$input = Input::getInstance();
		$value = rawurlencode('value [] =');
		$expectedValue = rawurldecode($value);
		$sanitizedValue = $input->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

	public function testSanitizeSpecialChars() {
		$input = Input::getInstance();
		$value = '<b>strong</b>';
		$expectedValue = htmlspecialchars($value);
		$sanitizedValue = $input->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

	public function testSanitizeArray() {
		$input = Input::getInstance();
		$value = array('foo' => ' bar ');
		$expectedValue = array('foo' => 'bar');
		$sanitizedValue = $input->sanitize($value);

		$this->assertEquals($expectedValue, $sanitizedValue);
	}

}