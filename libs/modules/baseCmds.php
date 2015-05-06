<?php

namespace ATFApp\Modules;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * abstract base cmd
 *
 */
abstract class BaseCmds {
	
	/**
	 * return cmd specific data
	 * 
	 * @return string
	 */
	public function getCmdData() {
		return "";
	}
	
	/**
	 * check access cmd
	 * 
	 * @return boolean
	 */
	public function canAccess() {
		$module = BasicFunctions::getModule();
		$cmd = BasicFunctions::getCmd();
		
		$moduleConfig = BasicFunctions::getConfig('modules');
		
		// check if authorization is required
		if (array_key_exists('access', $moduleConfig[$module]['cmds'][$cmd])) {
			$auth = Core\Auth::getInstance();
			
			if (!$auth->isLoggedIn()) {
				// authorization required but not logged in...
				return false;
			} elseif (!array_key_exists('groups', $moduleConfig[$module]['cmds'][$cmd]['access']) && !array_key_exists('users', $moduleConfig[$module]['cmds'][$cmd]['access'])) {
				// no users or groups defined - being logged in is enough
				return true;
			}
			
			// check for group restrictions
			if (array_key_exists('groups', $moduleConfig[$module]['cmds'][$cmd]['access']) ) {
				$groupGranted = false;
				// check group restrictions
				$accessGroups = $moduleConfig[$module]['cmds'][$cmd]['access']['groups'];
				if (is_array($accessGroups) && count($accessGroups) >= 1) {
					// check for group
					foreach ($accessGroups AS $group) {
						if ($auth->isGroupMember($group)) {
							$groupGranted = true;
							break;
						}
					}
				}
				
				if ($groupGranted) {
					return true;
				}
			}
			
			// check for user restrictions
			if (array_key_exists('users', $moduleConfig[$module]['cmds'][$cmd]['access'])) {
				$isUserRestricted = true;
				// check user restrictions
				$accessUsers = $moduleConfig[$module]['cmds'][$cmd]['access']['users'];
				if (is_array($accessUsers) && count($accessUsers) >= 1) {
					// check for userid
					$userGranted = false;
					$userId = $auth->getUserId();
					foreach ($accessUsers AS $user) {
						if ($user == $userId) {
							$userGranted = true;
							break;
						}
					}
					if ($userGranted) {
						return true;
					}
				}
			}
			
			return false;
		} else {
			// no authorization required for cmd
			return true;
		}
		return false;
	}
	
	
	// TODO test this function...
	/**
	 * forward to module
	 * 
	 * @param string $module
	 */
	protected function forwardToModule($module) {
		$forwarder = new Helper\Forward();
		$forwarder->forwardTo($module);
	}

	// TODO test this function...
	/**
	 * forward to module/cmd
	 * 
	 * @param string $cmd
	 * @param string $module
	 */
	protected function forwardToCmd($cmd, $module=null) {
		if (is_null($module)) $module = BasicFunctions::getModule();
		
		$forwarder = new Helper\Forward();
		$forwarder->forwardTo($module, $cmd);
	}
	
	/**
	 * forward to module/cmd/action
	 * 
	 * @param string $action
	 * @param string $cmd
	 * @param string $module
	 */
	protected function forwardToAction($action, $cmd= null, $module=null) {
		if (is_null($cmd)) $cmd = BasicFunctions::getCmd();
		if (is_null($module)) $module = BasicFunctions::getModule();
		
		$forwarder = new Helper\Forward();
		$forwarder->forwardTo($module, $cmd, $action);
	}

	/**
	 * respond to ajax request 
	 * types: json, xml
	 * 
	 * @param array $data
	 * @param string $type
	 * @param integer $cache
	 */
	protected function respondToAjaxRequest($data, $type="json", $cache=null) {
		switch ($type) {
			case "xml":
				if (!is_array($data)) {
					$data = array($data);	
				}
				$response = new Core\Response();
				$response->respondXml($data, $cache);
				break;
					
			case "text":
				// for simply returning a string
				$response = new Core\Response();
				$response->respondHtml($data); // used for a pure string instead of html
				break;
					
			case "json":
			default:
				if (!is_array($data)) {
					$data = array($data);
				}
				$response = new Core\Response();
				$response->respondJson($data, $cache);
		}
	}
}