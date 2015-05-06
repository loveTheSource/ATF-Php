<?php

namespace ATFApp\Core;

#use \helper as helper;
#use \core as core;
#use \model as model;

/**
 * autoloader for Core and Helper classes
 *
 * usage:
 * - require this class
 * - spl_autoload_register(array('HelperAutoload', 'loadClass'));
 */
class Autoload {
	
	private static $autoloadClasses = array();
	
	public function __construct() { }
	
	/**
	 * the autoloader itself
	 * determines and includes the class file
	 * 
	 * @param string $className
	 */
	public static function loadClass($class) {
		if (strpos($class, '\\') !== false) {
			$parts = explode('\\', $class);
			$prefix = $parts[count($parts)-2];
			$className = $parts[count($parts)-1];
	
			switch ($prefix) {
				case "Core":
					$file = CORE_PATH . lcfirst($className) . '.php';
					break;
					
				case "Helper":
					$file = HELPER_PATH . lcfirst($className) . '.php';
					break;
					
				case "Models":
					$file = MODELS_PATH . lcfirst($className) . '.php';
					break;
					
				default:
					$additionalAutoloader = self::getAutoloadClasses();
					if (array_key_exists($prefix, $additionalAutoloader)) {
						// additionally registered classes
						$file = $additionalAutoloader[$prefix] . lcfirst($className) . '.php';
					} else {
						throw new \Exception("Autoloader - invalid class: " . $class . ' / ' . $className);
					}
			}
			
		#	if (!is_file($file)) {
		#		throw new \Exception("Autoloader - file not found: " . $file . "(" . $prefix . ")");
		#	}
			require_once $file;
		}
	}
	
	/**
	 * get additional autoload classes
	 * 
	 * @return array
	 */
	private static function getAutoloadClasses() {
		return self::$autoloadClasses;
	}
	
	/**
	 * register additional autoload classes
	 * 
	 * @param string $subNamespace
	 * @param string $path
	 */
	public static function registerAutoloadClass($subNamespace, $path) {
		self::$autoloadClasses[$subNamespace] = $path;
	}
}