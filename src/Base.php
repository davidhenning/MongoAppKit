<?php

/**
 * Class Base
 *
 * Base provides core methods for all classes of MongoAppKit
 * 
 * @author David Henning <madcat.me@gmail.com>
 * 
 * @package MongoAppKit
 */

namespace MongoAppKit;

use Symfony\Component\HttpFoundation\Request;

class Base {

    /**
     * Config object
     * @var Config
     */

    protected $_oConfig = null;

    /**
     * Storage object
     * @var Storage
     */

    protected $_oStorage = null;

    /**
     * Request object
     * @var Symfony\Component\HttpFoundation\Request
     */

    protected $_oRequest = null;

    /**
     * Return Config object
     *
     * @return Config
     */

    public function getConfig() {
        if($this->_oConfig === null) {
            $this->_oConfig = Config::getInstance();
        }

        return $this->_oConfig;
    }

    /**
     * Return Storage object
     *
     * @return Storage
     */

    public function getStorage() {
        if($this->_oStorage === null) {
            $this->_oStorage = Storage::getInstance($this->getConfig());
        }
        
        return $this->_oStorage;       
    }

    /**
     * Return Request object
     *
     * @return Symfony\Component\HttpFoundation\Request
     */

    public function getRequest() {
        if($this->_oRequest === null) {
            $this->_oRequest = Request::createFromGlobals();
        }
        
        return $this->_oRequest; 
    }
}