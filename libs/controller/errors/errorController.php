<?php

namespace ATFApp\Controller\Errors;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;
use ATFApp\Core\Includer;

/**
 * error controller
 * 
 * must extend BaseController
 * (or implement all its public methods)
 *
 */
class ErrorController extends \ATFApp\Controller\BaseController {
	
	/**
	 * index action (404)
	 * 
	 * @return multitype:array|string
	 */
	public function get404Action() {
		// set 404 response status
		$response = Includer::getResponseObj();
		$response->setStatusCode(404);

		return ["msg" => BasicFunctions::getLangText('basics', 'message_404')];
		
		// forward to home instead of returning
		// $this->forwardToRoute('/');
	}

		/**
	 * index action (403)
	 * 
	 * @return multitype:array|string
	 */
	public function get403Action() {
		// set 403 response status
		$response = Includer::getResponseObj();
		$response->setStatusCode(403);
		
		return ["msg" => BasicFunctions::getLangText('basics', 'message_403')];
		
		// forward to home instead of returning
		// $this->forwardToRoute('/');
	}

	
}