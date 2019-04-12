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
	
	private $routes = null;	// routes from config

	// cache for successfully checked routes
	private $routesChecked = [];
	
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
	 *
	 * /some/path/?var1=abc&var2=123
	 */
	public function manageRoute() {
		$requestUri = $_SERVER['REQUEST_URI'];
		$tmpArr = explode("?", $requestUri);
		$requestPath = $tmpArr[0];
		if (substr($requestPath, 0, 1) === "/") {
			$requestPath = substr($requestPath, 1);
		}
		if (substr($requestPath, -1) === "/") {
			$requestPath = substr($requestPath, 0, -1);
		}
		$path = explode("/", $requestPath);	
		
		if ($this->checkRouteArray($path)) {
			$route = $this->getRouteString($path);
		} else {
			$route = ProjectConstants::ROUTE_404;
		}

		// check complete route
		if (!$this->checkRoute($route)) {
			throw new Exceptions\Custom("unable to determine route - 404 invalid", null, null, $_SERVER);
		}
	
		BasicFunctions::setRoute($route, true);
		$conf = $this->getRouteConfig($route);
		BasicFunctions::setModule($conf['module'], true);
	}
	
	/**
	 * get route string
	 * 
	 * @param array $route
	 * @return string
	 */
	private function getRouteString($route) {
		return '/' . implode('/', $route);
	}

	/**
	 * get route by array
	 * 
	 * @param array $route
	 * @param boolean $throwException
	 * @return boolean
	 */
	private function checkRouteArray($route, $throwException=false) {
		return $this->checkRoute($this->getRouteString($route), $throwException);
	}
	/**
	 * check route
	 *
	 * @param string $route
	 * @return boolean
	 */
	public function checkRoute($route, $throwException=false) {
		try {
			$allRoutes = $this->getAllRoutes();
			if (array_key_exists($route, $allRoutes)) {
				return true;
			}
			return false;
		} catch (\Exception $e) {
			if ($throwException && !BasicFunctions::isLive()) {
				throw $e;
			}
			return false;
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
	 * extract routes config recursively
	 */
	private function extractRoutes($conf, $pathStart='') {
		foreach($conf as $routeKey => $routeConf) {
			$this->routes[$pathStart . $routeKey] = $routeConf;
			$this->routes[$pathStart . $routeKey]['parentroute'] = $pathStart;
			if (array_key_exists('subroutes', $routeConf)) {
				unset($this->routes[$pathStart . $routeKey]['subroutes']);
				$this->extractRoutes($routeConf['subroutes'], $pathStart . $routeKey);
			}
		}
	}

	/**
	 * return config array of a route
	 * 
	 * @return array
	 */
	public function getRouteConfig($routeString) {
		$allRoutes = $this->getAllRoutes();
		if (array_key_exists($routeString, $allRoutes)) {
			return $allRoutes[$routeString];
		} else {
			if (!BasicFunctions::isLive()) {
				throw new Exceptions\Custom('cannot find route config: ' . $routeString);
			} else {
				BasicFunctions::doRedirect('/404');
			}
		}
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