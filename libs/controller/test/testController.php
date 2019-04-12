<?php

namespace ATFApp\Controller\Test;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;
use ATFApp\Models as Models;


/**
 * test controller
 * 
 * must extend BaseController
 * (or implement all its public methods)
 *
 */
class TestController extends \ATFApp\Controller\BaseController {
	
	/**
	 * index action 
	 * @return multitype:array|string
	 */
	public function indexAction() {
		return [];
	}

	public function testAction() {
		$auth = Core\Includer::getAuthObj();
		if ($auth->isLoggedIn()) {
			$userId = $auth->getUserId();   // method no longer available

			return ['userId' => $userId];
		} else {
			BasicFunctions::addMessage('info', 'login required... but not forced by access rule');
			return "";
		}
	}
	
}