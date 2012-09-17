<?php

namespace MongoAppKit\Tests;

use MongoAppKit\Encryption;

class EncryptionTest extends \PHPUnit_Framework_TestCase {

	public function testEncryption() {
		$encryption = new Encryption();
		$value = 'AES Test';
		$encryptedValue = $encryption->encrypt($value, 'foobar');

		$this->assertEquals($value, $encryption->decrypt($encryptedValue, 'foobar'));
	}
}