<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Encryption;

class EncryptionTest extends \PHPUnit_Framework_TestCase {

	public function testEncryption() {
		$encryption = Encryption::getInstance();
		$value = 'AES Test';
		$encryptedValue = $encryption->encrypt($value, 'foobar');

		$this->assertEquals($value, $encryption->decrypt($encryptedValue, 'foobar'));
	}

	public function testClone() {
		try {
			$encryption = Encryption::getInstance();
			$clone = clone $encryption;
		} catch(\Exception $e) {
			return;
		}

		$this->fail('Expected Exception was not thrown.');
	}
}