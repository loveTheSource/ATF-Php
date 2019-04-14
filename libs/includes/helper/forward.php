<?php

namespace ATFApp\Helper;

use ATFApp;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;


class Forward {
	
	public function __construct() { }
	
	/**
	 * forward to internal route
	 * 
	 * @param $route
	 */
	public function forwardTo($route) {
		if (is_null($route)) $route = ProjectConstants::ROUTE_DEFAULT;
		
		$router = Core\Includer::getRouter();
		$routeCheck = $router->checkRoute($route, true);
		if ($routeCheck === false) {
			throw new Exceptions\Custom("forward - invalid route: " . $route);
		} else {
			$counter = $this->countForward();
			$this->setForwarding();
			
			if ($counter > BasicFunctions::getConfig('project_config', 'forwarding_limit')) {
				throw new Exceptions\Custom("forward limit reached: " . $counter . ' - can be raised in project_config');
			} else {
				$this->performForward($route, $routeCheck);
			}
		}
	}
	
	private function performForward($route, $conf) {
		// renew document
		$this->renewDocument();
		
		// forward by re-running the project
		$project = new ATFApp\ATFProject();
		$project->run();
	}
	
	/**
	 * count forwarding
	 * 
	 * @return integer
	 */
	private static function countForward() {
		$forwardingCount = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_FORWARDING_COUNT);
		if (is_null($forwardingCount)) {
			$forwardingCount = 1;
		} else {
			$forwardingCount++;
		}
		
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_FORWARDING_COUNT, $forwardingCount);
		return $forwardingCount;
	}
	
	/**
	 * saves forwarding status globally
	 * 
	 * @param boolean $status
	 */
	private static function setForwarding($status=true) {
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_FORWARDING, $status);
	}
	
	/**
	 * renew document object (to remove existing settings)
	 */
	private function renewDocument() {
		// renew doc abject and set defaults
		$bootstrap = new Core\Bootstrap();
		$bootstrap->initDocument();
	}
}