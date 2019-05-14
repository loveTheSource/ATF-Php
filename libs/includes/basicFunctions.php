<?php

namespace ATFApp;

use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Helper AS Helper;


/**
 * basic functions of the application
 *
 * @access static
 */
class BasicFunctions {

	/**
	 * constructor 
	 * defined as private to force static usage
	 */
	private function __construct() { }
	
	/**
	 * redirect request
	 * 
	 * @param string $location
	 * @param integer $code
	 */
	public static function doRedirect($location, $code=null) {
		$redirector = new Helper\Redirect();
		$redirector->performRedirect($location, $code);
	}
	
	/**
	 * get config
	 * complete config or key only
	 *
	 * @param string $name
	 * @param string $key
	 * @return string|array
	 */
	public static function getConfig($name, $key=false) {
		$obj = Core\Factory::getConfigObj();
		if (!$key) {
			return $obj->getConfig($name);
		} else {
			return $obj->getConfigKey($name, $key);
		}
	}
	
	/**
	 * is forwarding
	 *
	 * @return boolean
	 */
	public static function isForwarding() {
		$globalForward = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_FORWARDING);
		return ($globalForward === true);
	}
		
	/**
	 * check if environment is production
	 * 
	 * @return boolean
	 */
	public static function isProduction() {
		return self::getEnv() == "production";
	}
	
	/**
	 * get ENVIRONMENT const
	 */
	public static function getEnv() {
		if (defined('ENVIRONMENT')) {
			return ENVIRONMENT;
		}
		return false;
	}

	/**
	 * whether the profiler should be used
	 * 
	 * @return boolean
	 */
	public static function useProfiler() {
		return (ProjectConstants::PROFILER_ENABLED === true && !self::isProduction());
	}
	
	# ++++++++++++++++++++++ language / skin ++++++++++++++++++++++
	
	/**
	 * get translation of an element
	 * 
	 * @param string $langPack
	 * @param string $key
	 * @param string|array $format
	 */
	public static function getLangText($langPack, $key, $format=false) {
		$obj = Core\Factory::getLangObj();
		return $obj->getLangText($langPack, $key, $format);
	}
	/**
	 * set current language
	 * 
	 * @param string $langCode
	 */
	public static function setLanguage($langCode) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_LANG, $langCode);
	}
	/**
	 * get current language
	 * 
	 * @return string|NULL
	 */
	public static function getLanguage() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_LANG);
	}
	
	/**
	 * set skin
	 * 
	 * @param string $skin
	 */
	public static function setSkin($skin) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_SKIN, $skin);
	}
	/**
	 * get skin
	 * 
	 * @return string
	 */
	public static function getSkin() {
		$skin = Core\Request::getParamSession(ProjectConstants::KEY_SESSION_SKIN);
		if (!is_null($skin)) {
			return $skin;
		} else {
			return self::getConfig('project_config', 'default_skin');
		}
	}
	
	# ++++++++++++++++++++++ token ++++++++++++++++++++++
	
	/**
	 * create a unique token 
	 * form hash to prevent double-clicking // double form submission via back button..
	 * 
	 * @param boolean $save
	 * @return string
	 */
	public static function createToken($save=true) {
		$token = md5( rand(100000, 999999) . Core\Request::getRemoteAddress() . microtime() );
		
		if ($save) {
			Core\Request::setParamSession(ProjectConstants::KEY_SESSION_TOKEN, $token);
		}
		
		return $token;
	}
	/**
	 * get token
	 * 
	 * @return string
	 */
	public static function getToken() {
		$token = Core\Request::getParamSession(ProjectConstants::KEY_SESSION_TOKEN);
		if (is_null($token)) {
			$token = self::createToken();
		}
		return $token;
	}
	/**
	 * check if given token matches session
	 * 
	 * @param string $tokenToCheck
	 * @return boolean
	 */
	public static function checkToken($tokenToCheck) {
		if (!is_null($tokenToCheck) && strlen($tokenToCheck) == 32) {
			$tokenSession = Core\Request::getParamSession(ProjectConstants::KEY_SESSION_TOKEN);
			
			if ($tokenSession == $tokenToCheck) {
				Core\Request::delParamSession(ProjectConstants::KEY_SESSION_TOKEN);
				return true;
			}
		}
		Core\Request::delParamSession(ProjectConstants::KEY_SESSION_TOKEN);
		return false;
	}
	
	
	/**
	 * get link to a certain module/cmd/action
	 *
	 * @param array $route
	 * @param boolean $absolute
	 * @param string $paramsString
	 * @return string
	 */
	public static function getLink($route, $absolute=true, $paramsString='') {
		if ($absolute) {
			$link = Core\Request::getHost();
		} else {
			$link = "";
		}
	
		if($route) {
			$link .= $route;
		}
	
		return $link . $paramsString;
	}


	# ++++++++++++++++++++++ routing ++++++++++++++++++++++	
	
	/**
	 * save route to session
	 * 
	 * @param string $route
	 */
	public static function setRouteSession($route) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_ROUTE, $route);
	}
	/**
	 * get route from session
	 * 
	 * @return string
	 */
	public static function getRouteSession() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_ROUTE);
	}
	
	/**
	 * set current route (global)
	 * 
	 * @param string $route
	 * @param boolean $saveToSession
	 */
	public static function setRoute($route, $saveToSession=false) {
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_ROUTE, $route);
		
		if ($saveToSession)
			self::setRouteSession($route);
	}
	/**
	 * get current route (global)
	 * 
	 * @return string
	 */
	public static function getRoute() {
		return Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_ROUTE);
	}

	/** ++++++++++++++++++++++ module ++++++++++++++++++++++++++++++ */

	/**
	 * save module to session
	 * 
	 * @param string $module
	 */
	public static function setModuleSession($module) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_MODULE, $module);
	}
	/**
	 * get module from session
	 * 
	 * @return string
	 */
	public static function getModuleSession() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_MODULE);
	}
	
	/**
	 * set current module (global)
	 * 
	 * @param string $module
	 * @param boolean $saveToSession
	 */
	public static function setModule($module, $saveToSession=false) {
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_MODULE, $module);
		
		if ($saveToSession)
			self::setModuleSession($module);
	}
	/**
	 * get current module (global)
	 * 
	 * @return string
	 */
	public static function getModule() {
		return Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_MODULE);
	}

	# ++++++++++++++++++++++ system messsages ++++++++++++++++++++++
	
	/**
	 * add new system message
	 * 
	 * @param string $type
	 * @param string $msg
	 * @param boolean $permanent
	 */
	public static function addMessage($type, $msg, $permanent=false) {
		$messages = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_SYSTEM_MSG);
		if (is_null($messages)) {
			$messages = [];
			
		}
		$messages[] = [
			'type' => $type,
			'msg' => $msg,
			'permanent' => $permanent
		];
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_SYSTEM_MSG, $messages);
	}
	
	/**
	 * add new system message from translation
	 * 
	 * @param string $type
	 * @param string $langPack
	 * @param string $langKey
	 * @param string|array $format
	 */
	public static function addMessageTranslation($type, $langPack, $langKey, $format=false) {
		$msg = self::getLangText($langPack, $langKey, $format);

		$messages = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_SYSTEM_MSG);
		if (is_null($messages)) {
			$messages = [];
			
		}
		$messages[] = [
			'type' => $type,
			'msg' => $msg,
			'permanent' => false
		];
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_SYSTEM_MSG, $messages);
	}
	
	/**
	 * get all system messages
	 * 
	 * @return array
	 */
	public static function getMessages() {
		$messages = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_SYSTEM_MSG);
		if (is_array($messages)) {
			return $messages;
		} else {
			return [];
		}		
	}
	
	/**
	 * save messages to session
	 * e.g. for redirecting
	 */
	public static function saveMessages() {
		$messages = self::getMessages();
		if (!is_null($messages)) {
			Core\Request::setParamSession(ProjectConstants::KEY_SESSION_SYSTEM_MSG, $messages);
		}
	}
	/**
	 * restore messages 
	 * e.g after redirect
	 */
	public static function restoreMessages() {
		$sessMsg = Core\Request::getParamSession(ProjectConstants::KEY_SESSION_SYSTEM_MSG);
		if (!is_null($sessMsg) && is_array($sessMsg)) {
			foreach($sessMsg AS $one)  {
				self::addMessage($one['type'], $one['msg']);
			}
			Core\Request::delParamSession(ProjectConstants::KEY_SESSION_SYSTEM_MSG);
		}
	}
	
	
	
	
}
