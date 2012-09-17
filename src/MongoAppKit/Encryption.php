<?php

namespace MongoAppKit;

class Encryption {

    protected $_oAes = null;

    /**
     * Constructor
     */

    public function __construct() {
        $this->_oAes = new \Crypt_AES();
    }

	public function encrypt($value, $key) {
		$this->_oAes->setKey($key);
        return base64_encode($this->_oAes->encrypt($value));
	}

	public function decrypt($value, $key) {
        $this->_oAes->setKey($key);
		return $this->_oAes->decrypt(base64_decode($value));
	}
}