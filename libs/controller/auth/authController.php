<?php

namespace ATFApp\Controller\Auth;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;
use ATFApp\Models as Model;

/**
 * auth controller
 * 
 * must extend BaseController
 * (or implement all its public methods)
 *
 */
class AuthController extends \ATFApp\Controller\BaseController {
	
	/**
	 * index action 
	 * @return multitype:array|string
	 */
	public function indexAction() {
		// forward to login action
		$this->forwardToRoute('/auth/login');

		// create new user
		$auth = Core\Factory::getAuthObj();
		$user = new Model\User();
		
		/*  // select all
		$all = $user->selectAll();
		echo '<pre>';var_dump($all);die();
		*/

		/*  // select one and delete
		$list = $user->selectByColumns(['login'=>'test']);
		$one = $list[0];
		var_dump($one);
		$res = $one->delete();
		var_dump($res);
		*/

		/*   // create user
		$user->login = "test";
		$user->password = $auth->getPasswordHash("abc123");
		$user->active = 1;
		$user->insert();
		*/
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
		return [];
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
		$this->forwardToRoute('/auth/login');
	}
	
	/**
	 * content to return, if login succeeded
	 * 
	 * @return string
	 */
	private function loggedIn() {
		return '<div>press <a href="/auth/logout">here</a> to logout...</div>';
	}
}