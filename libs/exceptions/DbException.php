<?php

namespace ATFApp\Exceptions;

class Db extends \Exception {
	
	private $additionalData = null;
	
	public function __construct($message=null, $code=null, $previous=null, $data=null) {
		$this->additionalData = $data;
		parent::__construct($message, $code, $previous);
	}
	
	public function getAdditionalData() {
		return $this->additionalData;
	}
}