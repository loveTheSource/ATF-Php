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
	 * check if environment is live
	 * 
	 * @return boolean
	 */
	public static function isLive() {
		if (defined('ENVIRONMENT')) {
			return ENVIRONMENT == "live";
		}
		return false;
	}
	
	/**
	 * whether the profiler should be used
	 * 
	 * @return boolean
	 */
	public static function useProfiler() {
		return (ProjectConstants::PROFILER_ENABLED === true && !self::isLive());
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
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_USER_LANG, $langCode);
	}
	/**
	 * get current language
	 * 
	 * @return string|NULL
	 */
	public static function getLanguage() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_USER_LANG);
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
	
	
	# ++++++++++++++++++++++ routing (module/cmd/action) ++++++++++++++++++++++
	
	/**
	 * get link to a certain module/cmd/action
	 *
	 * @param string $module
	 * @param string $cmd
	 * @param string $action
	 * @param boolean $absolute
	 * @param string $paramsString
	 * @return string
	 */
	public static function getLink($module, $cmd=null, $action=null, $absolute=true, $paramsString='') {
		if ($absolute) {
			$link = Core\Request::getHost() . "/";
		} else {
			$link = "/";
		}
	
		// add module
		$link .= $module . '/';
	
		// add cmd
		if (!is_null($cmd)) {
			$link .= $cmd . '/';
		}
	
		// add action
		if (!is_null($action)) {
			$link .= $action . '/';
		}
	
		return $link . $paramsString;
	}
	
	
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
	 * save cmd to session
	 * 
	 * @param string $cmd
	 */
	public static function setCmdSession($cmd) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_CMD, $cmd);
	}
	/**
	 * get cmd from session
	 * 
	 * @return string
	 */
	public static function getCmdSession() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_CMD);
	}
	/**
	 * save action to session
	 * 
	 * @param string $action
	 */
	public static function setActionSession($action) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_ACTION, $action);
	}
	/**
	 * get action from session
	 * 
	 * @return string
	 */
	public static function getActionSession() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_ACTION);
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
		return Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_MODULE);;
	}
	/**
	 * set current cmd (global)
	 * 
	 * @param string $cmd
	 * @param boolean $saveToSession
	 */
	public static function setCmd($cmd, $saveToSession=false) {
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_CMD, $cmd);
		
		if ($saveToSession)
			self::setCmdSession($cmd);
	}
	/**
	 * get current cmd (global)
	 * 
	 * @return string
	 */
	public static function getCmd() {
		return Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_CMD);;
	}
	/**
	 * set current action (global)
	 * 
	 * @param string $action
	 * @param boolean $saveToSession
	 */
	public static function setAction($action, $saveToSession=false) {
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_ACTION, $action);
		
		if ($saveToSession)
			self::setActionSession($action);
	}
	/**
	 * get current action (global)
	 * 
	 * @return string
	 */
	public static function getAction() {
		return Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_ACTION);
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
			$messages = array();
			
		}
		$messages[] = array(
			'type' => $type,
			'msg' => $msg,
			'permanent' => $permanent
		);
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
			return array();
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
	
	
	# ++++++++++++++++++++++ other ++++++++++++++++++++++
	
	/**
	 * set operator id 
	 * 
	 * @param integer $operatorId
	 */
	public static function setOperator($operatorId) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_OPERATOR, $operatorId);
	}
	
	/**
	 * get operator id 
	 * @return integer
	 */
	public static function getOperator() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_OPERATOR);
	}
	
	/**
	 * set operator name
	 *
	 * @param string $operatorName
	 */
	public static function setOperatorName($operatorName) {
		Core\Request::setParamSession(ProjectConstants::KEY_SESSION_OPERATOR_NAME, $operatorName);
	}
	
	/**
	 * get operator name
	 * 
	 * @return string
	 */
	public static function getOperatorName() {
		return Core\Request::getParamSession(ProjectConstants::KEY_SESSION_OPERATOR_NAME);
	}
	
	
}
