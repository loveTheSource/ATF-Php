<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;
use ATFApp\Models as Models;

/**
 * authentication class
 * 
 * use singleton via ::getInstance()
 * 
 * @author cre8.info
 */
class Auth {
	
	private static $instance = null;
	 
	// private to force singleton
	private function __construct() { }
	
	/**
	 * get object instance (singleton)
	 * 
	 * @return CoreAuth
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * check if user is logged in
	 * 
	 * @return boolean
	 */
	public function isLoggedIn() {
		if (!is_null(Core\Request::getParamSession(ProjectConstants::KEY_SESSION_USER_ID))) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * set url to redirect request after successful authentication
	 * 
	 * @param string $url
	 */
	public function setRedirectOnAuth($url) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_REDIRECT_ON_AUTH, $url);
	}
	/**
	 * get redirect url
	 * 
	 * @return null|string
	 */
	public function getRedirectOnAuth() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_REDIRECT_ON_AUTH);
	}
	/**
	 * remove redirect on auth
	 *
	 * @return null|string
	 */
	public function removeRedirectOnAuth() {
		Core\Request::delParamSession(ProjectConstants::KEY_SESSION_REDIRECT_ON_AUTH);
	}
	
	/**
	 * perform login 
	 * 
	 * @param integer $userId
	 * @param string $userLogin
	 * @param array $groups
	 */
	public function setLogin($userId, $userLogin, $groups=null) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_USER_ID, $userId);
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_USER_LOGIN, $userLogin);
		
		if (is_array($groups)) {
			Core\Request::setParamSession(ProjectConstants::KEY_SESSION_USER_GROUPS, $groups);
		}
	}
	
	/**
	 * perform logout
	 */
	public function setLogout() {
		Core\Request::delParamSession(ProjectConstants::KEY_SESSION_USER_ID);
		Core\Request::delParamSession(ProjectConstants::KEY_SESSION_USER_LOGIN);
		Core\Request::delParamSession(ProjectConstants::KEY_SESSION_USER_GROUPS);
		Core\Request::delParamSession(ProjectConstants::KEY_SESSION_MODEL_USER);
	}
	
	/**
	 * get user id from session
	 */
	public function getUserId() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_USER_ID);
	}
	
	/**
	 * get group memberships from session
	 * 
	 * @return array:
	 */
	public function getUserGroups() {
		$groups = Core\Request::getParamSession(ProjectConstants::KEY_SESSION_USER_GROUPS);
		if (is_array($groups)) {
			return $groups;
		}
		return array();
	}
	
	/**
	 * get user login from session
	 */
	public function getUserLogin() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_USER_LOGIN);
	}
	
	/**
	 * check if user is member of a group
	 * 
	 * @param integer $groupId
	 * @return boolean
	 */
	public function isGroupMember($groupId) {
		$userGroups = $this->getUserGroups();
		if (is_array($userGroups)) {
			if (in_array($groupId, $userGroups)) return true;
		}
		return false;
	}
	
	/**
	 * try to authenticate using login/pass
	 * 
	 * @param string $login
	 * @param string $pass
	 */
	public function checkLogin($login, $pass) {
		// TODO change to password_hash in PHP 5.5+
		// use hash_equals for Timing attack safe string comparison
		$salt = '$2a$07$' . $this->getSalt() . '$';
		$passCrypt = crypt($pass, $salt);
		$db = Core\Factory::getDbObj();
		
		$query = "SELECT 
				id, login, name, active, user_since , last_login, user_since  
				FROM users 
				WHERE login = " . $db->quote($login) . " && password = " . $db->quote($passCrypt) . "; ";
		
		$res =  $db->query($query);
		$arrayModels = $res->fetchALL(\PDO::FETCH_CLASS, 'ATFApp\Models\User');
		
		if (count($arrayModels) == 1) {
			$user = $arrayModels[0];
			$this->setLogin($user->id, $login);
			$groups = $this->setUserGroups();
			
			$user->setUserGroups($groups);
			$this->setUser($user);
			$this->setLastLogin();
			
			return true;
		}
		return false;
	}
	
	private function setLastLogin() {
		$userId = $this->getUserId();
		
		$db = Core\Factory::getDbObj();
		$query = "UPDATE users SET last_login = current_timestamp() WHERE id = " . $db->quote($userId);
		
		$res = $db->query($query);
	}
	
	private function setUserGroups() {
		$userId = $this->getUserId();
		
		$db = Core\Factory::getDbObj();
		$query = "SELECT ug.group AS id, g.groupname, g.active
				FROM user_groups AS ug
				LEFT JOIN groups AS g
				ON g.id = ug.group
				WHERE ug.user = " . $db->quote($userId);

		$res = $db->query($query);
		$arrayModels = $res->fetchALL(\PDO::FETCH_CLASS, 'ATFApp\Models\Group');
				
		$groups = array();
		foreach ($arrayModels AS $group) {
			$groups[] = $group->id;
		}
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_USER_GROUPS, $groups);
		
		return $arrayModels;
	}
	
	private function setUser(Models\User $user) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_MODEL_USER, $user);
	}
	
	/**
	 * get ModelUser from session
	 * 
	 * @return ModelUser
	 */
	public function getUser() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_MODEL_USER);
	}
	
	private function getSalt() {
		$salt = trim(file_get_contents(INCLUDES_PATH . 'slt.txt'));
		if (empty($salt)) {
			throw new Exceptions\Core("auth security: cannot load salt");
		}
		return $salt;
	}
}