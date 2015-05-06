<?php

namespace ATFApp\Modules\ModuleTest;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;
use ATFApp\Models as Models;


/**
 * cmd index 
 * 
 * must extend BaseCmds
 * (or implement all its public methods)
 *
 */
class CmdIndex extends \ATFApp\Modules\BaseCmds {
	
	/**
	 * index action 
	 * @return multitype:array|string
	 */
	public function indexAction() {
		// forward to test action
		$this->forwardToAction('test');
	}

	public function testAction() {
		$auth = Core\Includer::getAuthObj();
		if ($auth->isLoggedIn()) {
			$user = $auth->getUser();

			return var_export(get_object_vars(new Models\User()), true).'<br/>'.
					var_export($user, true);
		} else 
			return "not logged in. required for test.. ";
	}
	
	// save new user name... testing..
	public function nameAction() {
		$auth = CoreIncluder::getAuthObj();
		if ($auth->isLoggedIn()) {
			$user = $auth->getUser();
			
			$newName = substr($user->name, 0, 5) . rand(10000, 99999);
			
			$user->__set('name', $newName);
			$user->updateAll();
			
			return var_export($user, true).'<br/>';
		} else 
			return "not logged in. required for test.. ";		
	}
	
	public function namespaceAction() {
		$this->forwardToCmd('index', 'auth');
	}
}