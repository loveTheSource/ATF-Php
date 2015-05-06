<?php

namespace ATFApp\Modules;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * abstract base module
 *
 */
abstract class BaseModules {
	
	/**
	 * return module specific data
	 * 
	 * @return string
	 */
	public function getModuleData() {
		return "";
	}
	
	/**
	 * check access
	 * 
	 * @return boolean
	 */
	public function canAccess() {
		$module = BasicFunctions::getModule();
		
		$moduleConfig = BasicFunctions::getConfig('modules');
		
		// check if authorization is required
		if (array_key_exists('access', $moduleConfig[$module])) {
			$auth = Core\Auth::getInstance();
			
			if (!$auth->isLoggedIn()) {
				// authorization required but not logged in...
				return false;
			} elseif (!array_key_exists('groups', $moduleConfig[$module]['access']) && !array_key_exists('users', $moduleConfig[$module]['access'])) {
				// no users or groups defined - being logged in is enough
				return true;
			}
			
			// check for group restrictions
			if (array_key_exists('groups', $moduleConfig[$module]['access']) ) {
				$groupGranted = false;
				// check group restrictions
				$accessGroups = $moduleConfig[$module]['access']['groups'];
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
			if (array_key_exists('users', $moduleConfig[$module]['access'])) {
				$userGranted = false;
				// check user restrictions
				$accessUsers = $moduleConfig[$module]['access']['users'];
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
			// no authorization required for module
			return true;
		}
		return false;
	}
	
	/**
	 * forward to module
	 * 
	 * @param string $module
	 */
	protected function forwardToModule($module) {
		$forwarder = new HelperForward();
		$forwarder->forwardTo($module);
	}
	
}