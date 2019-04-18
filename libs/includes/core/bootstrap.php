<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * initialize project
 * 
 * each public method starting with 'init' will be executed
 * 
 */
class Bootstrap {
	
	public function __construct() { }

	/**
	 * some basic includes
	 */
	public function initIncludes() {
		require_once INCLUDES_PATH . 'basicFunctions.php';
		require_once CONFIG_PATH . 'base' . DIRECTORY_SEPARATOR . 'constants.php';
	}
	
	/**
	 * check for maintenance mode and redirect if set
	 */
	public function initMaintenance() {
		if (ProjectConstants::MAINTENANCE_MODE === true) {
			$maintenanceFile = 'maintenance.html';
			readfile(HTDOCS_PATH . $maintenanceFile);
			exit();
		}
	}
	
	/**
	 * start profiler
	 */
	public function initProfiler() {
		require_once HELPER_PATH . 'profiler.php';
		if (BasicFunctions::useProfiler()) {
			declare(ticks=1);
			Helper\Profiler::startProfiler();
		}
	}
	
	/**
	 * register autoload function
	 */
	public function initAutoloader() {
		require_once CORE_PATH . 'autoload.php';
		spl_autoload_extensions(".php");
		spl_autoload_register(['ATFApp\Core\Autoload', 'loadClass']);
	}
	
	/**
	 * manage session
	 */
	public function initSession() {
		$status = session_status();
		if ($status == PHP_SESSION_DISABLED) {
			throw new Exceptions\Custom("sessions are disabled");
		} elseif ($status == PHP_SESSION_NONE) {
			if (!session_start()) {
				throw new Exceptions\Custom("unable to start session");
			}
		} // otherwise session is already started
		
		$secondsRenew = 120; // seconds until session id will be regenerated
		$secondsTimeout = 1800; // seconds for the session to timeout
		$iniMaxLifetime = ini_get("session.gc_maxlifetime");
		if ($iniMaxLifetime && (int)$iniMaxLifetime < $secondsTimeout) {
			// use value from ini
			$secondsTimeout = (int)$iniMaxLifetime;
		}
		$now = time();
		
		// check for session timeout
		if (!isset($_SESSION['session_timeout']) || $now > (int)$_SESSION['session_timeout']) {
			// destroy and restart session
			session_unset();
			session_destroy();
			session_start();
		}
		
		// check for session renew
		if(isset($_SESSION['session_renew'])) {
			if($now > (int)$_SESSION['session_renew']) {
				// renew session
				session_regenerate_id(true);
				// update session renew
				$_SESSION['session_renew'] = $now + $secondsRenew;
			}
		} else {
			session_regenerate_id(true);
			// update session renew
			$_SESSION['session_renew'] = $now + $secondsRenew;
		}
		 
		// update session timeout
		$_SESSION['session_timeout'] = $now + $secondsTimeout;
	}
	
	/**
	 * manage routing (module/cmd/action)
	 * e.g.
	 * 
	 * /some/route/:var1
	 */
	public function initRouting() {
		$router = Core\Includer::getRouter();
		$router->manageRoute();
	}

	/**
	 * restore the system messages from session
	 */
	public function initRestoreMessages() {
		BasicFunctions::restoreMessages();
	}
	
	/**
	 * set language for request
	 */
	public function initLanguage() {
		$langObj = Core\Lang::getInstance();
		$currentLang = BasicFunctions::getLanguage();
		if (!$currentLang) {
			$langObj->setDefaultLanguage();
		}
	}
	
	/**
	 * initialize document
	 */
	public function initDocument() {
		$doc = Core\Document::getInstance(true);
		// set document title
		$doc->setTitle(ProjectConstants::PROJECT_NAME);
		// set favicon
		$doc->setFavicon("/favicon.png");
		// set main css
		$doc->addCssFile('style.css');
	}

	
	public function initCsrfProtection() {
		if (ProjectConstants::CSRF_FORCE_VALIDATION) {
			if (Core\Request::isPostRequest()) {
				try {
					$csrfToken = Core\Request::getParamPost(ProjectConstants::CSRF_POST_PARAM);
					/** @var \ATFApp\Helper\CsrfTokens $csrfHelper */
					$csrfHelper = Core\Factory::getHelper('csrfTokens');
					if (!$csrfToken || !$csrfHelper->validateToken($csrfToken)) {
						BasicFunctions::addMessage('error', "Token invalid");
						$router = Core\Includer::getRouter();
						$conf403 = $router->checkRoute(ProjectConstants::ROUTE_403, true);
					}
				} catch(\Throwable $e) {
					die("token invalid");
				}
			}
		}
	}

}