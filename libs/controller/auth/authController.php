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
			$this->redirect('/');
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
						$this->redirect('/');
					}
				} else {
					BasicFunctions::addMessage('error', BasicFunctions::getLangText('auth', 'msg_login_failed'));
				}
			}
		}
		
		// render login form template
		$tokenHelper = Core\Factory::getHelper('csrfTokens');
		return [
			'token' => $tokenHelper->getNewToken()
		];
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
		$this->redirect('/auth/login');
	}

}