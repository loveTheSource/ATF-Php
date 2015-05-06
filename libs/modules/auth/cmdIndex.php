<?php

namespace ATFApp\Modules\ModuleAuth;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * cmd index (in module auth)
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
		// forward to login action
		$this->forwardToAction('login');
	}
	
	/**
	 * login action
	 * send login form and check result
	 * 
	 * @throws CustomException
	 * @return string|array:
	 */
	public function loginAction() {
		$auth = Core\Factory::getAuthObj();
		$loginSuccess = false;
		if ($auth->isLoggedIn()) {
			// already logged in
			return $this->loggedIn();
		} else {
			$login = Core\Request::getParamPost('auth_login');
			$pass = Core\Request::getParamPost('auth_password');
			if (!empty($login) && !empty($pass)) {
				// login form sent
				$loginSuccess = $auth->checkLogin($login, $pass);
				
				if ($loginSuccess) {
					BasicFunctions::addMessage('success', BasicFunctions::getLangText('auth', 'msg_login_success'));
					if ($url = $auth->getRedirectOnAuth()) {
						$auth->removeRedirectOnAuth();
						BasicFunctions::doRedirect($url, 303);
					} else {
						// return empty string to prevent template rendering
						return $this->loggedIn();
					}
				} else {
					BasicFunctions::addMessage('error', BasicFunctions::getLangText('auth', 'msg_login_failed'));
				}
			}
		}
		
		// render login form template
		return array();
	}
	
	/**
	 * perform logout
	 */
	public function logoutAction() {
		$auth = Core\Includer::getAuthObj();
		if ($auth->isLoggedIn()) {
			$auth->setLogout();
		}
		BasicFunctions::addMessage('success', BasicFunctions::getLangText('auth', 'msg_logout_success'));
		// forward to login
		$this->forwardToAction('login');
	}
	
	/**
	 * content to return, if login succeeded
	 * 
	 * @return string
	 */
	private function loggedIn() {
		return '<div>press <a href="/auth/index/logout">here</a> to logout...</div>';
	}
}