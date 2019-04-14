<?php

namespace ATFApp\Core;

require_once CONTROLLER_PATH . "baseController.php";

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

class Factory extends Includer {
	
	/**
	 * get a controller object
	 * 
	 * @throws CustomException
	 * @return \ATFApp\Controller\BaseController (extended)
	 */
	public static function getController()  {
		try {
			$router = Includer::getRouter();
			$routeConfig = $router->getCurrentRouteConfig();
			$module = (array_key_exists('module', $routeConfig)) ? $routeConfig['module'] : null;
			$file = $router->getControllerFile($routeConfig['controller'], $module);
			$class = $router->getControllerNamespace($module) . $routeConfig['controller'];
			require_once $file;
			$obj = new $class();
			return $obj;
		} catch (\Exception $e) {
			throw $e;
		}
	}
	
	
	/**
	 * get module object
	 * 
	 * @param string $route
	 * @throws CustomException
	 * @return \ATFApp\Controller\BaseModule (extended)
	 */
	public static function getModule($module)  {
		try {
			$router = Includer::getRouter();
			$file = $router->getModuleFile($module);
			$class = $router->getControllerNamespace($module) . ucfirst($module) . 'Module';
			if (file_exists($file)) {
				require_once $file;
				$obj = new $class();
				return $obj;
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * get a helper object
	 * might not be required if static use available
	 * 
	 * @param string $helper
	 * @throws Exception
	 * @return helper object
	 */
	public static function getHelper($helper) {
		try {
			$class = 'Helper\\' . ucfirst($helper);
			$obj = new $class();
			return $obj;
		} catch (\Exception $e) {
			throw $e;
		}
	}
	
}