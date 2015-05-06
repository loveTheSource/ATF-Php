<?php

namespace ATFApp\Helper;

use ATFApp;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;


class Forward {
	
	private $initWWVetCssMethod = "initWWVetCss";
	
	public function __construct() { }
	
	public function forwardTo($module, $cmd=null, $action=null) {
		if (is_null($cmd)) $cmd = ProjectConstants::DEFAULT_CMD;
		if (is_null($action)) $action = ProjectConstants::DEFAULT_ACTION;
		
		$router = Core\Router::getInstance();
		if (!$router->checkRoute($module, $cmd, $action)) {
			throw new Exceptions\Custom("forward - invalid routing: " . $module . '/' . $cmd . '/' . $action);
		} else {
			$counter = $this->countForward();
			$this->setForwarding();
			
			if ($counter > BasicFunctions::getConfig('project_config', 'forwarding_limit')) {
				throw new Exceptions\Custom("forward limit reached: " . $counter);
			} else {
				$this->performForward($action, $cmd, $module);
			}
		}
	}
	
	private function performForward($action, $cmd=null, $module=null) {
		BasicFunctions::setAction($action);
		BasicFunctions::setCmd($cmd);
		BasicFunctions::setModule($module);
		
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
		
		$method = $this->initWWVetCssMethod;
		if (method_exists($bootstrap, $method)) {
			$bootstrap->$method();
		}
	}
}