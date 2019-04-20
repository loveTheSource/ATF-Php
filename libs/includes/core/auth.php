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
	 * @return \ATFApp\Core\Auth
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
	public function setLogin($userId, $groups=null) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_USER_ID, $userId);
		
		if (is_array($groups)) {
			Core\Request::setParamSession(ProjectConstants::KEY_SESSION_USER_GROUPS, $groups);
		}
	}
	
	/**
	 * perform logout
	 */
	public function setLogout() {
		Core\Request::delParamSession(ProjectConstants::KEY_SESSION_USER_ID);
		Core\Request::delParamSession(ProjectConstants::KEY_SESSION_USER_GROUPS);
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
	 * @return array
	 */
	public function getUserGroups() {
		$groups = Core\Request::getParamSession(ProjectConstants::KEY_SESSION_USER_GROUPS);
		if (is_array($groups)) {
			return $groups;
		}
		return [];
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
		$db = Core\Factory::getDbObj();
		
		$query = "SELECT *
				FROM users 
				WHERE login = " . $db->quote($login) . "; ";
		
		$res =  $db->query($query);

		if ($res !== false) {
			$arrayModels = $res->fetchALL(\PDO::FETCH_CLASS, 'ATFApp\Models\User');
			
			if (count($arrayModels) == 1) {
				$user = $arrayModels[0];
				if (password_verify($pass, $user->password)) {
					$this->setLogin($user->id, $login);
					$groups = $this->setUserGroups();
					
					$user->setUserGroups($groups);
					$this->setLastLogin($user);
					
					return true;
				}
			}
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
		}

		return false;
	}
	
	/**
	 * save last login to db
	 * 
	 * @param \ATFApp\Models\User $user
	 */
	private function setLastLogin(\ATFApp\Models\User $user) {
		$user->last_login = date('Y-m-d H:i:s');
        $user->update(['last_login']);
	}
	
	/**
	 * fetch user groups and save them to session
	 */
	private function setUserGroups() {
		$userId = $this->getUserId();
		
		$db = Core\Factory::getDbObj();
		$query = "SELECT ug.group_id AS id, g.groupname, g.active
				FROM user_groups AS ug
				LEFT JOIN groups AS g
				ON g.id = ug.group_id
				WHERE ug.user_id = " . $db->quote($userId);

		$res = $db->query($query);
		if ($res !== false) {
			$arrayModels = $res->fetchALL(\PDO::FETCH_CLASS, 'ATFApp\Models\Group');
				
			$groups = [];
			foreach ($arrayModels AS $group) {
				$groups[] = $group->id;
			}
			Core\Request::setParamSession(ProjectConstants::KEY_SESSION_USER_GROUPS, $groups);
			
			return $arrayModels;	
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
		}
	}
	
	/**
	 * get password hash
	 * 
	 * @param string $password
	 * @return string
	 */
	public function getPasswordHash($password) {
		return password_hash($password, PASSWORD_DEFAULT);
	}

}