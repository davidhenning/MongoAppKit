<?php

namespace MongoAppKit;

class Encryption {

    protected $_aes = null;

    /**
     * Constructor
     */

    public function __construct() {
        $this->_aes = new \Crypt_AES();
    }

	public function encrypt($value, $key) {
		$this->_aes->setKey($key);
        return base64_encode($this->_aes->encrypt($value));
	}

	public function decrypt($value, $key) {
        $this->_aes->setKey($key);
		return $this->_aes->decrypt(base64_decode($value));
	}
}