<?php

namespace ATFApp\Core;

require_once MODULES_PATH . "baseModules.php";
require_once MODULES_PATH . "baseCmds.php";

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

class Factory extends Includer {
	
	/**
	 * get a module object
	 * 
	 * @param string $module
	 * @throws CustomException
	 * @return ModulesBase (extended)
	 */
	public static function getModule($module)  {
		try {
			$router = Router::getInstance();
			if ($router->moduleExists($module)) {
				$file = $router->getModuleFile($module);
				$class = $router->getModuleNamespace() . $router->getModuleClass($module);
				require_once $file;
				$obj = new $class();
				return $obj;
			} else {
				throw new Exceptions\Custom("invalid module: " . $module);
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * get a cmd object
	 * 
	 * @param string $module
	 * @param string $cmd
	 * @throws CustomException
	 * @return CmdsBase (extended)
	 */
	public static function getCmd($module, $cmd) {
		try {
			$router = Router::getInstance();
			if ($router->cmdExists($module, $cmd)) {
				$file = $router->getCmdFile($module, $cmd);
				$class = $router->getCmdNamespace($module) . $router->getCmdClass($cmd);
				require_once $file;
				$obj = new $class();
				return $obj;
			} else {
				throw new Exceptions\Custom("invalid cmd: " . $module . '/' . $cmd);
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