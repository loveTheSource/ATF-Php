<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

class Lang {

	private $langPacks = array();
	private $langCodes = null;

	private static $instance = null;
	
	// private to enforce singleton usage
	private function __construct() {}
	
	/**
	 * get object instance (singleton)
	 * 
	 * @return Core\Lang
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * return current language
	 * @return string
	 */
	public function getLanguage() {
		return BasicFunctions::getLanguage();
	}
	/**
	 * set language 
	 * @param string $langCode
	 */
	public function setLanguage($langCode=null) {
		if ( !is_null($langCode) && ($this->languageCodeEnabled($langCode) || $langCode == ProjectConstants::DEFAULT_LANG)) {
			BasicFunctions::setLanguage($langCode);
		} else {
			$this->setDefaultLanguage();
		}
	}
	public function setDefaultLanguage() {
		BasicFunctions::setLanguage(ProjectConstants::DEFAULT_LANG);
	}
	
	public function getLangCodes() {
		if ( is_null($this->langCodes) ) {
			$this->langCodes = $this->loadLanguageList();
		}
		return $this->langCodes;
	}
	
	public function getLangText($pack, $key, $format=false) {
		if ( !array_key_exists($pack, $this->langPacks) ) {
			// try to load lang pack
			$this->loadLangPack($pack);
		}
		// check for existing language pack
		if ( !array_key_exists($pack, $this->langPacks) ) {
			return "-" . $pack . "-" . $key;
		} else {
			if ( !array_key_exists($key, $this->langPacks[$pack]) ) {
				$this->reportMissingTranslation($pack, $key);
				return $pack . "-" . $key;
			} elseif ($format!=false) {
				// return formated string
				if (is_array($format)) {
					return vsprintf($this->langPacks[$pack][$key], $format);
				} elseif (is_string($format) || is_numeric($format)) {
					return sprintf($this->langPacks[$pack][$key], $format);
				} else {
					// return without format
					return $this->langPacks[$pack][$key] . '-';
				}
			} else {
				// return translation
				return $this->langPacks[$pack][$key];
			}
		}
	}

	public function isLangText($pack, $key) {
		if ( !array_key_exists($pack, $this->langPacks) ) {
			$this->loadLangPack($pack);
		}
		if ( !array_key_exists($pack, $this->langPacks) ) {
			return false;
		} else {
			if ( !array_key_exists($key, $this->langPacks[$pack]) ) {
				return false;
			} else {
				return true;
			}
		}
	}

	private function loadLangPack($pack, $lang=false) {
		if (!$lang) {
			$lang = $this->getLanguage();
		}
		$file = LANG_PATH . $lang . DIRECTORY_SEPARATOR . $pack . ".lng";
		if (is_file($file)) {
			if ($packData = parse_ini_file($file)) {
				$this->langPacks[$pack] = $packData;
				return true;
			} else {
				throw new Exceptions\Custom("unable to parse language pack: " . $file);
			}
		} else {
			throw new Exceptions\Custom("language pack missing: " . $file);
		}
	}

	private function languageCodeEnabled($langCode) {
		$allLangCodes = $this->getLangCodes();
		if (isset($allLangCodes[$langCode]) && isset($allLangCodes[$langCode]['enabled']) && $allLangCodes[$langCode]['enabled'] == 1) {
			return true;
		} else {
			return false;
		}
	}

	private function loadLanguageList() {
		/*
		$confObj = basic_includer::getConfigObj();
		$conf = $confObj->getConfig('languages_config');
		if ( !is_null($conf) ) {
			return $conf;
		} else {
			return array();
		}
		*/
		return array('de' => array('enabled'=>'1', 'native_name'=>'deutsch') );
	}

	// TODO report missing translation
	private function reportMissingTranslation($pack, $key) {
		// ...
	}
	
	
}

?>