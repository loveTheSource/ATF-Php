<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

/**
 * Core\Router
 * manage application routing
 * 
 * @author cre8.info
 *
 */
class Router {
	
	private static $instance = null;
	
	private $config = null;  // modules config
	
	// cache for checked modules/cmds/actions
	private $modulesChecked = array();
	private $cmdsChecked = array();
	private $actionsChecked = array();  // valid routes
	
	// private to force singleton
	private function __construct() {
		$this->config = BasicFunctions::getConfig('modules');
	}
	
	/**
	 * get object instance (singleton)
	 * 
	 * @return Core\Router
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * manage routing (module/cmd/action)
	 * e.g.
	 *
	 * /module/cmd/action?var1=abc&var2=123
	 *
	 * missing values will be set to default:
	 * /module/cmd/ 	=> /module/cmd/index
	 * /module/ 		=> /module/index/index
	 * / 				=> /index/index/index
	 *  
	 */
	public function manageRoute() {
		$requestDirs = $_SERVER['REQUEST_URI'];
		$dirs = explode(DIRECTORY_SEPARATOR, $requestDirs);
	
		$module = ProjectConstants::DEFAULT_MODULE;
		$cmd = ProjectConstants::DEFAULT_CMD;
		$action = ProjectConstants::DEFAULT_ACTION;
		// 1: module, 2: cmd, 3: action
		for ($i=1; $i<=3; $i++) {
			if (isset($dirs[$i])) {
				$routeStep = trim(strtolower($dirs[$i]));
				if ($routeStep && preg_match('/^[\w\-]+$/', $routeStep)) {
					switch ($i) {
						case 1:  // module
							$module = $routeStep;
							break;
								
						case 2:  // cmd
							$cmd = $routeStep;
							break;
	
						case 3:  // action
							$action = $routeStep;
							break;
					}
				} else {
					break;
				}
			} else {
				// route ended early => using defaults
				break;
			}
		}
		
		if (!$this->checkRoute($module, $cmd, $action)) {
			$module = ProjectConstants::MODULE_404;
			$cmd = ProjectConstants::DEFAULT_CMD;
			$action = ProjectConstants::DEFAULT_ACTION;
		}
		// check complete route
		if (!$this->checkRoute($module, $cmd, $action, true)) {
			throw new Exceptions\Custom("unable to determine route - 404 invalid", null, null, $_SERVER);
		}
	
		BasicFunctions::setModule($module, true);
		BasicFunctions::setCmd($cmd, true);
		BasicFunctions::setAction($action, true);
	}
	
	/*
	public function manageRoute() {
		$requestDirs = $_SERVER['REQUEST_URI'];
		$dirs = explode(DIRECTORY_SEPARATOR, $requestDirs);
		
		$module = ProjectConstants::DEFAULT_MODULE;
		$cmd = ProjectConstants::DEFAULT_CMD;
		$action = ProjectConstants::DEFAULT_ACTION;
		// 1: module, 2: cmd, 3: action
		for ($i=1; $i<=3; $i++) {
			if (isset($dirs[$i])) {
				$routeStep = trim(strtolower($dirs[$i]));
				if ($routeStep && preg_match('/^[\w\-]+$/', $routeStep)) {
					switch ($i) {
						case 1:  // module
							if ($this->moduleExists($routeStep)) {
								$module = $routeStep;
							} else {
								// no further checks once the module failed
								break 2;
							}
							break;
					
						case 2:  // cmd
							if ($this->cmdExists($module, $routeStep)) {
								$cmd = $routeStep;
							} else {
								// no further checks once the cmd failed
								break 2;
							}
							break;
								
						case 3:  // action
							if ($this->actionExists($module, $cmd, $routeStep)) {
								$action = $routeStep;
							} else {
								// no further checks once the action failed
								break 2;
							}
							break;
					}
				} else {
					break;
				}
			} else {
				// route ended early => using defaults
				break;
			}
		}
		
		// check complete route
		if (!$this->checkRoute($module, $cmd, $action, true)) {
			throw new Exceptions\Custom("unable to determine route", null, null, $_SERVER['REQUEST_URI']);
		}
		
		BasicFunctions::setModule($module, true);
		BasicFunctions::setCmd($cmd, true);
		BasicFunctions::setAction($action, true);
	}
	*/
	/**
	 * wrapper for actionExists
	 * 
	 * @param string $module
	 * @param string $cmd
	 * @param string $action
	 */
	public function checkRoute($module, $cmd, $action, $throwException=false) {
		// actionExists also checks module and cmd
		return $this->actionExists($module, $cmd, $action, $throwException=false);
	}

	/**
	 * get module file (incl path)
	 * 
	 * @param string $module
	 * @return string
	 */
	public function getModuleFile($module) {
		return MODULES_PATH . "module" . ucfirst($module) . ".php";
	}
	/**
	 * get cmd file (incl path)
	 * 
	 * @param string $module
	 * @param string $cmd
	 * @return string
	 */
	public function getCmdFile($module, $cmd) {
		return MODULES_PATH . $module . DIRECTORY_SEPARATOR . "cmd" . ucfirst($cmd) . ".php";
	}
	/**
	 * get module classname
	 * 
	 * @param string $module
	 * @return string
	 */
	public function getModuleClass($module) {
		return 'Module' . ucfirst($module);
	}
	/**
	 * get cmd classname
	 * 
	 * @param string $cmd
	 * @return string
	 */
	public function getCmdClass($cmd) {
		return 'Cmd' . ucfirst($cmd);
	}
	/**
	 * get action method name
	 * 
	 * @param string $action
	 * @return string
	 */
	public function getActionMethod($action) {
		return $action . 'Action';
	}
	
	/**
	 * check module
	 *
	 * @param string $module
	 * @return boolean
	 */
	public function moduleExists($module, $throwException=false) {
		try {
			$moduleKey = $this->getKey($module);
			if (in_array($moduleKey, $this->modulesChecked)) {
				return true;
			} else {
				if (isset($this->config[$module])) {
					$file = $this->getModuleFile($module);
					$class = $this->getModuleNamespace() . $this->getModuleClass($module);
					if (is_file($file)) {
						require_once $file;
						$obj = new $class();
						if (is_object($obj)) {
							$this->modulesChecked[] = $moduleKey;
							return true;
						} elseif($throwException)
							throw new Exceptions\Custom("invalid module - cannot create instance: " . $module);
					} elseif($throwException) {
						throw new Exceptions\Custom("invalid module - file not found: " . $module);
					}
						
				} elseif($throwException)
					throw new Exceptions\Custom("invalid module - not found in config: " . $module);
			}
			
			return false;
		} catch (\Exception $e) {
			if (!BasicFunctions::isLive()) {
				throw $e;
			}
			return false;
		}
	}
	
	/**
	 * check cmd
	 *
	 * @param string $module
	 * @param string $cmd
	 * @return boolean
	 */
	public function cmdExists($module, $cmd, $throwException=false) {
		try {
			$cmdKey = $this->getKey($module, $cmd);
			if (in_array($cmdKey, $this->cmdsChecked)) {
				return true;
			} elseif ($this->moduleExists($module, $throwException)) {
				if (isset($this->config[$module]['cmds'][$cmd])) {
					$file = $this->getCmdFile($module, $cmd);
					if (is_file($file)) {
						$this->cmdsChecked[] = $cmdKey;
						return true;
					} elseif($throwException)
						throw new Exceptions\Custom("invalid cmd - file not found: " . $module . '/' . $cmd);
				} elseif($throwException)
					throw new Exceptions\Custom("invalid cmd - not found in config: " . $module . '/' . $cmd);
			} elseif($throwException)
				throw new Exceptions\Custom("invalid cmd - not found in config: " . $module . '/' . $cmd);
			
			return false;
		} catch (\Exception $e) {
			if (!BasicFunctions::isLive()) {
				throw $e;
			}
			return false;
		}
	}
	
	/**
	 * check action
	 *
	 * @param string $module
	 * @param string $cmd
	 * @param string $action
	 * @return boolean
	 */
	public function actionExists($module, $cmd, $action, $throwException=false) {
		try {
			$actionKey = $this->getKey($module, $cmd, $action);
			if (in_array($actionKey, $this->actionsChecked)) {
				return true;
			} elseif ($this->moduleExists($module, $throwException) && $this->cmdExists($module, $cmd, $throwException)) {
				if (array_key_exists($action, $this->config[$module]['cmds'][$cmd]['actions'])) {
					$file = $this->getCmdFile($module, $cmd);
					$class = $this->getCmdNamespace($module) . $this->getCmdClass($cmd);
					$method = $this->getActionMethod($action);
					
					if (is_file($file)) {
						require_once $file;
						$obj = new $class();
						if (is_object($obj)) {
							if (method_exists($obj, $method)) {
								$this->actionsChecked[] = $actionKey;
								return true;
							} elseif($throwException)
								throw new Exceptions\Custom("invalid action - method not found: " . $module . '/' . $cmd . '/' . $action);
						} elseif($throwException)
							throw new Exceptions\Custom("invalid action - cannot create instance: " . $module . '/' . $cmd . '/' . $action);
					} elseif($throwException)
						throw new Exceptions\Custom("invalid action - file not found: " . $module . '/' . $cmd . '/' . $action);
				} elseif($throwException)
					throw new Exceptions\Custom("invalid action - not found in config: " . $module . '/' . $cmd . '/' . $action);
			}
			
			return false;
		} catch (\Exception $e) {
			if (!BasicFunctions::isLive()) {
				throw $e;
			}
			return false;
		}
	}
	

	/**
	 * get module namespace
	 *
	 * @param string $cmd
	 * @return string
	 */
	public function getModuleNamespace() {
		return 'ATFApp\Modules' . '\\';
	}

	/**
	 * get cmd namespace
	 *
	 * @param string $cmd
	 * @return string
	 */
	public function getCmdNamespace($module=null) {
		if (is_null($module)) $cmd = BasicFunctions::getModule();
	
		return 'ATFApp\Modules\\Module' . ucfirst($module) . '\\';
	}
	
	/**
	 * get the routing key
	 * 
	 * @param string $module
	 * @param string $cmd
	 * @param string $action
	 * @return string
	 */
	private function getKey($module, $cmd=null, $action=null) {
		$key = $module;
		if (!is_null($cmd)) {
			$key .= '_' . $cmd;
			
			if (!is_null($action)) {
				$key .= '_' . $action;
			}
		} 
		return $key;
	}
}