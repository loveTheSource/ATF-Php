<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

/**
 * config loader class
 * (singleton)
 * 
 */
class Config {
	
	private static $obj = null;
	private $configs = [];  // collection of all loaded configs
	
	private function __construct() {}
	
	/**
	 * get object instance (singleton)
	 * 
	 * @return CoreConfig
	 */
	public static function getInstance() {
		if (is_null ( self::$obj )) {
			self::$obj = new self();
		}
		return self::$obj;
	}
	
	/**
	 * load config file
	 * 
	 * @param string $name
	 * @throws CustomException
	 * @return array
	 */
	public function getConfig($name) {
		if (substr($name, -7) != "_config") {
			$name .= "_config";
		}
		if (!array_key_exists($name, $this->configs)) {
			// load default config file
			$defaultConfigFile = CONFIG_PATH . $name . ".default.php";
			if (is_file($defaultConfigFile)) {
				$configDataDefault = include $defaultConfigFile;
			} else {
				$configDataDefault = [];
			}
			// load custom config file and merge
			$configFile = CONFIG_PATH . $name . ".php";
			if (is_file ( $configFile )) {
				$configDataCustom = include $configFile;
				#$configData = array_merge_recursive($configDataDefault, $configDataCustom);
				$configData = $this->mergeConfigsRecursive($configDataDefault, $configDataCustom);
				$this->configs[$name] = $configData;
				return $this->configs[$name];
			} else {
				throw new Exceptions\Custom( "config file missing: " . $configFile );
			}
		} else {
			return $this->configs[$name];
		}
	}
	
	/**
	 * get a certain config key
	 * 
	 * @param string $name
	 * @param string $key
	 * @return Ambigous <>|NULL
	 */
	public function getConfigKey($name, $key) {
		$config = $this->getConfig ( $name );
		if ($config) {
			if (array_key_exists ( $key, $config )) {
				return $config [$key];
			}
		}
		return null;
	}

	/**
	 * recursively merge two config arrays
	 * 
	 * @param array $defaultConfig
	 * @param array $customConfig
	 * @return array
	 */
	private function mergeConfigsRecursive($defaultConfig, $customConfig) {
		foreach ($customConfig AS $key => $value) {
			if (is_array($value)) {
				if (array_key_exists($key, $defaultConfig)) {
					$defaultConfig[$key] = $this->mergeConfigsRecursive($defaultConfig[$key], $customConfig[$key]);
				} else {
					$defaultConfig[$key] = $customConfig[$key];
				}				
			} else {
				$defaultConfig[$key] = $value;
			}
		}
		return $defaultConfig;
	}
	
}

?>