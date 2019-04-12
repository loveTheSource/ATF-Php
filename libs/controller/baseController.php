<?php

namespace ATFApp\Controller;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;
use ATFApp\Core\Includer;

/**
 * abstract base controller
 *
 */
abstract class BaseController {
	
	/**
	 * check access to route (and parent routes)
	 * 
	 * @return boolean
	 */
	public function canAccess() {
		$route = BasicFunctions::getRoute();
		
		$router = Core\Includer::getRouter();
		$routeConfig = $router->getRouteConfig($route);

		return $this->canAccessRoute($routeConfig);
	}
	
	/**
	 * check if route is accessible
	 */
	private function canAccessRoute($routeConfig) {
		// parent route
		$parentRoute = $routeConfig['parentroute'];

		// check if authorization is required
		if (array_key_exists('access', $routeConfig)) {
			$auth = Core\Auth::getInstance();
			
			if (!$auth->isLoggedIn()) {
				// authorization required but not logged in...
				return false;
			} elseif (!array_key_exists('groups', $routeConfig['access']) && !array_key_exists('users', $routeConfig['access'])) {
				// no users or groups defined - being logged in is enough
				return $this->canAccessParentRoute($parentRoute);
			}
			
			// check for group restrictions
			if (array_key_exists('groups', $routeConfig['access']) ) {
				$groupGranted = false;
				// check group restrictions
				$accessGroups = $routeConfig['access']['groups'];
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
					return $this->canAccessParentRoute($parentRoute);
				}
			}
			
			// check for user restrictions
			if (array_key_exists('users', $routeConfig['access'])) {
				// check user restrictions
				$accessUsers = $routeConfig['access']['users'];
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
						return $this->canAccessParentRoute($parentRoute);
					}
				}
			}
			
			return false;
		} else {
			// no authorization required for route
			return $this->canAccessParentRoute($parentRoute);
		}
		return false;
	}

	private function canAccessParentRoute($parentRoute) {
		if (empty($parentRoute)) {
			return true;
		} else {
			$router = Core\Includer::getRouter();
			$parentRouteConfig = $router->getRouteConfig($parentRoute);

			return $this->canAccessRoute($parentRouteConfig);
		}
	}

	/**
	 * forward to module
	 * 
	 * @param string $route
	 */
	protected function forwardToRoute($route) {
		$forwarder = new Helper\Forward();
		$forwarder->forwardTo($route);
	}


	/**
	 * respond to request 
	 * types: json, xml, html/text
	 * 
	 * @param array $data
	 * @param string $type
	 * @param integer $cache
	 */
	protected function respondToRequest($data, $type="json", $cache=null) {
		$response = Includer::getResponseObj();
		switch ($type) {
			case "xml":
				if (!is_array($data)) {
					$data = [$data];	
				}
				$response->respondXml($data, $cache);
				break;
					
			case "text":
				// for simply returning a string
				$response->respondHtml($data);
				break;
					
			case "json":
			default:
				if (!is_array($data)) {
					$data = [$data];
				}
				$response->respondJson($data, $cache);
		}
	}
}