<?php

namespace MongoAppKit\Exceptions;

use \Exception;

class HttpException extends Exception {

	protected $_oCallingObject = null;

	public function getCallingObject() {
		return $this->_oCallingObject;
	}

	public function setCallingObject($oObject) {
		$this->_oCallingObject = $oObject;
	}
}