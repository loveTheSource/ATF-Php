<?php

namespace ATFApp\Helper;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;
use ATFApp\Core\Includer;

/**
 * HelperRedirect
 * 
 * 303	See Other
 * 307 	Temporary Redirect
 * 308 	Permanent Redirect
 * 
 * @author cre8.info
 */
class Redirect {
	
	public $defaultRedirect = 307;
	public $supportedCodes = [303, 307, 308];
	
	public function __construct() { }
	
	/**
	 * redirect to another module/cmd/action
	 * 
	 * @param string $module
	 * @param string $cmd
	 * @param string $action
	 * @param integer $code
	 */
	/*
	public function redirectTo($module, $cmd=null, $action=null, $code=null) {
		$url = BasicFunctions::getLink($module, $cmd, $action);
		
		$this->performRedirect($url, $code);
	}
	*/

	/**
	 * perform http redirect using CoreReponse class
	 * 
	 * @param string $url
	 * @param integer $code
	 */
	public function performRedirect($url, $code=null) {
		if (is_null($code) || !in_array($code, $this->supportedCodes)) {
			$code = $this->defaultRedirect;
		}
		
		// save system messages
		BasicFunctions::saveMessages();
		
		$response = Includer::getResponseObj();
		if (!is_null($code)) {
			$response->setStatusCode($code);
		}
		$response->respondRedirect($url);
	}
}