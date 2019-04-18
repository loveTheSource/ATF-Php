<?php

namespace ATFApp\Core;

use ATFApp\ProjectConstants;

/**
 * Core\Request
 * 
 * access methods static
 * 
 */
class Request {
	
	# ++++++++++++++++++++++ request (method, url, domain, referer, ..) ++++++++++++++++++++++
	
	/**
	 * get request method
	 *
	 * @return string
	 */
	public static function getRequestMethod() {
		return $_SERVER['REQUEST_METHOD'];
	}
	/**
	 * is post request
	 *
	 * @return boolean
	 */
	public static function isPostRequest() {
		return self::getRequestMethod() == "POST";
	}
	/**
	 * is get request
	 *
	 * @return boolean
	 */
	public static function isGetRequest() {
		return self::getRequestMethod() == "GET";
	}
	
	
	
	/**
	 * return the host incl protocol
	 *
	 * @return string
	 */
	public static function getHost($addProtocol=true) {
		$host = "";
		if ($addProtocol) {
			$host = 'http';
			if (array_key_exists('HTTPS', $_SERVER) && !empty($_SERVER['HTTPS'])) {
				$host .= "s";
			}
			$host .= "://";
		}
		
		$host .= self::getDomain();
		return $host;
	}
	
	/**
	 * return current domain
	 * (without leading protocol)
	 *
	 * @param boolean $stripWww
	 * @return string
	 */
	public static function getDomain($stripWww=false) {
		$domain = $_SERVER['SERVER_NAME'];
		if ($_SERVER['SERVER_PORT'] != "80") {
			$domain .= ':'.$_SERVER["SERVER_PORT"];
		}
		if ($stripWww) {
			// remove leading 'www.' if present
			if (strtolower(substr($domain, 0, 4)) == "www.") {
				$domain = substr($domain, 4);
			}
		}
		return $domain;
	}
	
	/**
	 * return complete request url
	 *
	 * @param boollean $absolute
	 * @param boolean $includeQueryString
	 * @return string
	 */
	public static function getRequestURL($absolute=true, $includeQueryString=false) {
		$requestURL = "";
		if ($absolute) {
			$requestURL = self::getHost();
		}
	
		if ($includeQueryString) {
			$requestURL .= $_SERVER['REQUEST_URI'];
		} else {
			$queryStringLength = strlen($_SERVER['QUERY_STRING']);
			if ($queryStringLength == 0) {
				$requestURL .= $_SERVER['REQUEST_URI'];
			} else {
				$requestURL .= substr($_SERVER['REQUEST_URI'], 0, -($queryStringLength+1));
			}
		}
	
		return $requestURL;
	}
	
	/**
	 * return referer url (if set)
	 *
	 * @return string|NULL
	 */
	public static function getHttpReferer() {
		if (array_key_exists('HTTP_REFERER', $_SERVER)) {
			return $_SERVER['HTTP_REFERER'];
		} else {
			return null;
		}
	}
	
	/**
	 * try to determine remote address as good as possible
	 * better not rely on it - only use for statistics
	 *
	 * @param boolean $remoteAddressOnly
	 * @return string
	 */
	public static function getRemoteAddress($remoteAddressOnly=false) {
		if (!$remoteAddressOnly && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			if (strpos(",", $_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ipsArray = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($ipsArray AS $ip) {
					$ip = trim($ip);
					if (!empty($ip) && substr($ip, 0, 8) != "192.168." && substr($ip, 0, 3) != "10.") {
						return $ip;
						break;
					}
				}
			}
		}
		return $_SERVER['REMOTE_ADDR'];
	}
	
	
	
	# ++++++++++++++++++++++ params (get/post/session/globals/cookie) ++++++++++++++++++++++
	
	/**
	 * return get param
	 *
	 * @param string $key get key
	 * @parma boolean $sanitize strip tags for xss prevention
	 * @return unknown|NULL
	 */
	public static function getParamGet($key, $sanitize=true) {
		if (array_key_exists($key, $_GET)) {
			if ($sanitize) {
				if (!is_array($_GET[$key])) {
					return filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
				} else {
					return filter_var_array($_GET[$key], FILTER_SANITIZE_STRING, true);
				}
			} else {
				return $_GET[$key];
			}
		}
		return null;
	}
	/**
	 * delete get param
	 *
	 * @param string $key
	 */
	public static function delParamGet($key) {
		$_GET[$key] = null;
	}
	
	/**
	 * return post param
	 *
	 * @param string $key post key
	 * @parma boolean $sanitize strip tags for xss prevention
	 * @return unknown|NULL
	 */
	public static function getParamPost($key, $sanitize=true) {
		if (array_key_exists($key, $_POST)) {
			if ($sanitize) {
				if (!is_array($_POST[$key])) {
					return filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
				} else {
					return filter_var_array($_POST[$key], FILTER_SANITIZE_STRING, true);
				}
			} else {
				return $_POST[$key];
			}
		}
		return null;
	}
	/**
	 * delete post param
	 *
	 * @param string$key
	 */
	public static function delParamPost($key) {
		$_POST[$key] = null;
	}
	
	/**
	 * return session value
	 *
	 * @param string $key
	 * @return unknown|NULL
	 */
	public static function getParamSession($key) {
		if (array_key_exists($key, $_SESSION)) {
			return $_SESSION[$key];
		}
		return null;
	}
	/**
	 * save to session
	 *
	 * @param string $key
	 * @param unknown $value
	 */
	public static function setParamSession($key, $value) {
		$_SESSION[$key] = $value;
	}
	/**
	 * delete from session
	 *
	 * @param string $key
	 */
	public static function delParamSession($key) {
		if (array_key_exists($key, $_SESSION)) {
			unset($_SESSION[$key]);
		}
	}
	
	/**
	 * return globals param
	 *
	 * @param string $key
	 * @return unknown|NULL
	 */
	public static function getParamGlobals($key) {
		if (array_key_exists($key, $GLOBALS)) {
			return $GLOBALS[$key];
		}
		return null;
	}
	/**
	 * set globals param
	 *
	 * @param string $key
	 * @param unknown $value
	 */
	public static function setParamGlobals($key, $value) {
		$GLOBALS[$key] = $value;
	}
	/**
	 * delete from globals array
	 *
	 * @param string $key
	 */
	public static function delParamGlobals($key) {
		if (isset($GLOBALS[$key])) {
			unset($GLOBALS[$key]);
		}
	}
	
	public static function setParamRoute($key, $value) {
		if (!array_key_exists(ProjectConstants::KEY_GLOBAL_ROUTE_PARAMS, $GLOBALS)) {
			$GLOBALS[ProjectConstants::KEY_GLOBAL_ROUTE_PARAMS] = [];
		}
		$GLOBALS[ProjectConstants::KEY_GLOBAL_ROUTE_PARAMS][$key] = filter_var($value, FILTER_SANITIZE_STRING);
	}
	public static function getParamRoute($key) {
		if (array_key_exists(ProjectConstants::KEY_GLOBAL_ROUTE_PARAMS, $GLOBALS)) {
			if (isset($GLOBALS[ProjectConstants::KEY_GLOBAL_ROUTE_PARAMS][$key])) {
				return $GLOBALS[ProjectConstants::KEY_GLOBAL_ROUTE_PARAMS][$key];
			}
		}
		return null;
	}
	public static function deleteParamsRoute() {
		$GLOBALS[ProjectConstants::KEY_GLOBAL_ROUTE_PARAMS] = [];
	}

	/**
	 * set user cookie
	 *
	 * @param string $key
	 * @param string $value
	 * @param number $expire
	 * @param string $path
	 */
	public static function setUserCookie($key, $value, $expire=0, $path="/") {
		if (!headers_sent()) {
			setcookie($key, $value, $expire, $path);
		}
	}
	/**
	 * get cookie value
	 *
	 * @param string $key
	 * @parma boolean $sanitize strip tags for xss prevention
	 * @return string|NULL
	 */
	public static function getUserCookie($key, $sanitize=true) {
		if (array_key_exists($key, $_COOKIE)) {
			if ($sanitize) {
				if (!is_array($_COOKIE[$key])) {
					return filter_input(INPUT_COOKIE, $key, FILTER_SANITIZE_STRING);
				} else {
					return filter_var_array($_COOKIE[$key], FILTER_SANITIZE_STRING, true);
				}
			} else {
				return $_COOKIE[$key];
			}
		}
		return null;
	}
	/**
	 * delete user cookie / set expired
	 *
	 * @param string $key
	 * @param string $path
	 */
	public static function deleteUserCookie($key, $path=null) {
		if (!headers_sent()) {
			$expire = time() - 3600;
			if (!is_null($path)) {
				setcookie($key, "", $expire, $path);
			} else {
				setcookie($key, "", $expire);
			}
		}
	}
	
	
}