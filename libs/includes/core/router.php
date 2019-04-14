<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Core;

/**
 * Core\Router
 * manage application routing
 * 
 * @author cre8.info
 *
 */
class Router {
	/**
	 * required route keys
	 * will throw exception if missing
	 */
	private $routeKeysRequired = ['module', 'controller', 'action', 'template'];
	
	private static $instance = null;
	
	private $config = null;  // modules config
	
	private $routes = null;	// routes from config

	private $currentRoute = null;
	private $currentRouteConfig = null;

	// private to force singleton
	private function __construct() {
		$this->config = BasicFunctions::getConfig('routes');
	}
	
	/**
	 * get object instance (singleton)
	 * 
	 * @return \ATFApp\Core\Router
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * manage routing
	 * e.g.
	 * /some/path/:someRouteParam/?var1=abc&var2=123
	 */
	public function manageRoute() {
		$requestUri = preg_replace('/[^\w\d\-\/\?\=\&]/si', '', $_SERVER['REQUEST_URI']); // you never know ;)
		$requestPath = explode("?", $requestUri)[0];
		if (substr($requestPath, -1) === "/") {
			$requestPath = substr($requestPath, 0, -1);
		}

		$routeChecked = $this->checkRoute($requestPath, true);
		if ($routeChecked !== false) {
			$route = $requestPath;
		} else {
			$route = ProjectConstants::ROUTE_404;

			// check complete route
			if ($this->checkRoute($route, true) === false) {
				throw new Exceptions\Custom("unable to determine route - 404 invalid", null, null, $_SERVER);
			}
		}
	
		$conf = $this->getCurrentRouteConfig($route);

		if (array_key_exists('routeparams', $conf)) {
			$this->extractRouteParams($requestPath, $conf['routeparams']);
		}
	}


	/**
	 * validate route string
	 * 
	 * @param string $route
	 * @param array $baseConf
	 * @param int $depth
	 * @return boolean|array false or the route config
	 */
	private function validateRoute($route, $baseConf, $depth=0) {
		$routeParts = explode('/', $route);
		if ($routeParts === false || empty($routeParts)) {
			$routeParts = [''];
		} elseif (count($routeParts) > 1 && $routeParts[0] === '') {
			array_shift($routeParts);
		}

		if (isset($routeParts[$depth])) {
			$currentPart = '/' . $routeParts[$depth];
			if (array_key_exists($currentPart, $baseConf)) {
				if (count($routeParts) === $depth + 1) {
					// last part of the route => matched
					return $baseConf[$currentPart];
				} elseif (count($routeParts) > $depth + 1) {
					// end of route not reached yet
					if (array_key_exists('subroutes', $baseConf[$currentPart])) {
						if (isset($routeParts[$depth + 1])) {
							// more route parts to check available
							return $this->validateRoute($route, $baseConf[$currentPart]['subroutes'], $depth + 1);
						}
					} else {
						// no subroutes, although the end of the route is not reached yet
						// will return false
					}
				}
			} else {
				foreach ($baseConf as $subKey => $subConf) {
					if (strpos($subKey, $currentPart. "/:") === 0) {
						// route params found
						$routeParamsParts = explode('/:', $subKey);
						array_shift($routeParamsParts);

						$depthPlusRouteParams = $depth + 1 + count($routeParamsParts);
						if (count($routeParts) === $depthPlusRouteParams) {
							// number of params matches
							return $subConf;
						} elseif (count($routeParts) > $depthPlusRouteParams) {
							// end of route not reached yet
							if (array_key_exists('subroutes', $subConf)) {
								return $this->validateRoute($route, $subConf['subroutes'], $depthPlusRouteParams);
							} else {
								// no subroutes, although the end of the route is not reached yet
								// will return false
							}
						}

						break;  // break loop
					}
				}
			}	
		}

		return false;
	}

	/**
	 * save current route
	 * in this (singleton) object
	 * and
	 * in session
	 * 
	 * @param string $routeString
	 * @param array $routeConf
	 */
	private function setCurrentRoute($routeString, array $routeConf) {
		$this->currentRoute = $routeString;
		$this->currentRouteConfig = $routeConf;
		BasicFunctions::setRoute($routeString);
		BasicFunctions::setModule($routeConf['module']);
	}

	/**
	 * check route
	 *
	 * @param string $route
	 * @param boolean $setAsCurrent
	 * @return boolean|array false or the route config
	 */
	public function checkRoute($route, $setAsCurrent=false) {
		$allRoutes = $this->getAllRoutes();
		$validRoute = $this->validateRoute($route, $allRoutes);
		if ($validRoute === false) {
			return false;
		}

		if ($setAsCurrent) {
			$this->setCurrentRoute($route, $validRoute);
		}

		return $validRoute;
	}
	
	/**
	 * extract route params from route
	 * 
	 * @param string $requestPath
	 * @param array $expectedParams
	 */
	private function extractRouteParams($requestPath, $expectedParams) {
		if (substr($requestPath, 0, 1) === "/") {
			$requestPath = substr($requestPath, 1);
		}
		$pathArray = explode('/', $requestPath);
		$pathLength = count($pathArray);
		foreach(array_reverse($expectedParams) as $i => $paramName) {
			Core\Request::setParamRoute($paramName, $pathArray[$pathLength - ($i + 1)]);
		}
	}

	/**
	 * get all routes
	 * 
	 * @return array
	 */
	private function getAllRoutes() {
		if (is_null($this->routes)) {
			$this->extractRoutes($this->config);
		}
		return $this->routes;
	}
	

	/**
	 * extract main routes config 
	 * 
	 * loop over the main routes to extract subroutes
	 * 
	 * @param array $conf
	 */
	private function extractRoutes($conf) {
		$this->routes = $conf;
		foreach($this->routes as $mainKey => $mainConf) {
			$this->extractSubRoutes($this->routes[$mainKey], $mainKey);
		}
	}

	/**
	 * extract subroutes config recursively
	 * 
	 * @param array $routeConf
	 * @param string $routeKey
	 * @param array $parents
	 */
	private function extractSubRoutes(&$routeConf, $routeKey, array $parents=[]) {
		foreach ($this->routeKeysRequired as $requiredKey) {
			if (!array_key_exists($requiredKey, $routeConf)) {
				throw new Exceptions\Custom('invalid route config. missing "' . $requiredKey . '" for route: ' . $routeKey);
			}
		}
		$routeParams = [];
		$routeParamsArray = explode('/:', $routeKey);
		foreach($routeParamsArray as $i => $param) {
			if ($i !== 0) {
				if (substr($param, -1) === '/') {
					$param = substr($param, 0, -1);
				}
				$routeParams[] = $param;
			}
		}
		$routeConf['routeparams'] = $routeParams;

		$routeConf['parents'] = $parents;

		if (array_key_exists('subroutes', $routeConf)) {
			foreach($routeConf['subroutes'] as $subKey => $subConf) {
				$tmpParents = $parents;
				$tmpParents[] = $routeKey;
				$this->extractSubRoutes($routeConf['subroutes'][$subKey], $subKey, $tmpParents);
			}	
		}
	}

	/**
	 * return config array of current route
	 * 
	 * @return array
	 */
	public function getCurrentRouteConfig() {
		if (!$this->currentRouteConfig) {
			throw new Exceptions\Custom("current route config not found");
		}
		return $this->currentRouteConfig;
	}

	
	/**
	 * get module file (incl path)
	 * 
	 * @param string $module
	 * @return string
	 */
	public function getModuleFile($module) {
		// the module lies inside a folder of the same name
		return CONTROLLER_PATH . strtolower($module) . DIRECTORY_SEPARATOR . $module . "Module.php";
	}
	
	/**
	 * get controller file (incl path)
	 * 
	 * @param string $controller
	 * @param string $module
	 * @return string
	 */
	public function getControllerFile($controller, $module='') {
		$file = CONTROLLER_PATH;
		if ($module) {
			$file .= $module . DIRECTORY_SEPARATOR;
		}
		$file .= lcfirst($controller) . ".php";

		return $file;
	}
	
	/**
	 * get controller/module namespace
	 *
	 * @param string $module
	 * @return string
	 */
	public function getControllerNamespace($module) {
		$namespace = 'ATFApp\Controller' . '\\';
		if ($module) {
			$namespace .= ucfirst($module) . '\\';
		}
		return $namespace;
	}

	
}