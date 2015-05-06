<?php

namespace ATFApp;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

/**
 * main project class (bootstrap, run project, ...)
 * 
 * @author cre8.info
 */
class ATFProject {
	
	public function __construct() { }
	
	/**
	 * initialize project
	 * 
	 * @throws Exception
	 */
	public function init() {
		try {
			$this->bootstrap();
		} catch (\Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * Run, Forrest, Run!
	 */
	public function run() {
		try {
			$handler = new Core\Handler();
			$handler->handle();
			$handler->postActions();
		} catch (\Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * perform the bootstrap
	 */
	private function bootstrap() {
		require_once CORE_PATH . 'bootstrap.php';
		$bootstrapObj = new Core\Bootstrap();
		foreach (get_class_methods($bootstrapObj) AS $method) {
			// execute all methods that match init* 
			if (substr($method, 0, 4) == "init") {
				$bootstrapObj->$method();
			}
		}
	}
	
}