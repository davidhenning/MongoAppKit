<?php

namespace MongoAppKit;

class Encryption {

    /**
     * Config object
     * @var Config
     */

    private static $_oInstance = null;

    /**
     * Return instance of class Config
     *
     * @return Config
     */

    public static function getInstance() {
        if(self::$_oInstance === null) {
            self::$_oInstance = new Encryption();
        }

        return self::$_oInstance;
    }

    protected $_oAes = null;

    /**
     * Constructor
     */

    private function __construct() {
        $this->_oAes = new \Crypt_AES();
    }

    /**
     * Prohibit cloning of the class object (Singleton pattern)
     */

    public function __clone() {
        return null;
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