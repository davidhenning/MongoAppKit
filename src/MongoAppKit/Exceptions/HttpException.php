<?php

namespace MongoAppKit\Exceptions;

use \Exception;

class HttpException extends Exception {

	protected $_oCallingObject = null;

	public function getCallingObject() {
		return $this->_oCallingObject;
	}

	public function setCallingObject($object) {
		$this->_oCallingObject = $object;
	}
}