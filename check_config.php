<?php

/**
 * check the modules config
 * 
 * usage (cli):
 * > php check_config.php
 * 
 * 
 */

namespace ATFApp;

session_start();

chdir (realpath('www'));

require 'config/base/main_config.php';
require 'config/base/main_config.default.php';

require_once INCLUDES_PATH . 'basicFunctions.php';
require_once CONFIG_PATH . 'base' . DIRECTORY_SEPARATOR . 'constants.php';

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Core as Core;

require_once CORE_PATH . 'autoload.php';
spl_autoload_extensions(".php");
spl_autoload_register(array('ATFApp\Core\Autoload', 'loadClass'));

$router = Core\Router::getInstance();
$template = Core\Factory::getTemplateObj();

$configObj = Core\Factory::getConfigObj();
$modules = $configObj->getConfig('modules');

$valid = true;
$errors = array();

// return path to action template
function getActionTemplatePath($template, $module, $cmd, $action) {
	return $template->getTemplatePath().
		'modules' . DIRECTORY_SEPARATOR .
		$module . DIRECTORY_SEPARATOR .
		'cmds' . DIRECTORY_SEPARATOR .
		$cmd . DIRECTORY_SEPARATOR .
		'actions' . DIRECTORY_SEPARATOR .
		$action . ".phtml";
}


# checking php files/classes/methods

foreach ($modules AS $moduleId => $moduleData) {
	$file = $router->getModuleFile($moduleId);
	$class = $router->getModuleNamespace() . $router->getModuleClass($moduleId);
	if (!is_file($file)) {
		$valid = false;
		$errors[] = $moduleId."// - module file not found: ".$file;
	} else {
		try {
			require_once $file;
			if (!class_exists($class)) {
				$valid = false;
				$errors[] = $moduleId."// - class '".$class."' does not exists in file: ".$file;
			} else {
				$obj = new $class();
				unset($obj);
			}				
		} catch (\Exception $e) {
			$valid = false;
			$errors[] = $moduleId."// - exception: ".$e->getMessage();
		}
	}
	
	// cmds
	if (!isset($moduleData['cmds']) || empty($moduleData['cmds'])) {
		$valid = false;
		$errors[] = "Module: ".$moduleId." - no cmds found...";
	} else {
		foreach ($moduleData['cmds'] AS $cmdId => $cmdData) {
			$cmdFile = $router->getCmdFile($moduleId, $cmdId);
			$cmdClass = $router->getCmdNamespace($moduleId) . $router->getCmdClass($cmdId);
			$cmdValid = true;
			
			if (!is_file($cmdFile)) {
				$valid = false;
				$errors[] = $moduleId."/".$cmdId."/ - cmd file not found: ".$cmdFile;
			} else {
				try {
					$fileContent = file_get_contents($cmdFile);
					$needleNamespace = 'namespace ATFApp\Modules\Module'.ucfirst($moduleId).';'; // search for correct namespace
					$needleClassname = "class Cmd".ucfirst($cmdId)." extends "; // search for correct classname
					if (strpos($fileContent, $needleNamespace) === false) {
						$valid = false;
						$errors[] = $moduleId."/".$cmdId."/ - namespace probably invalid in file: ".$cmdFile." / expected: ".$needleNamespace;
						$cmdValid = false;
					} elseif (strpos($fileContent, $needleClassname) === false) {
						$valid = false;
						$errors[] = $moduleId."/".$cmdId."/ - classname probably invalid in file: ".$cmdFile." / expected: ".$needleClassname."[...]";
						$cmdValid = false;
					} else {
						require_once $cmdFile;
						if (!class_exists($cmdClass)) {
							$valid = false;
							$errors[] = $moduleId."/".$cmdId."/ - class '".$cmdClass."' does not exists in file: ".$cmdFile;
							$cmdValid = false;
						} else {
							$obj = new $cmdClass();
							unset($obj);
						}
					}
				} catch (\Exception $e) {
					$valid = false;
					$errors[] = $moduleId."/".$cmdId."/ - exception: ".$e->getMessage();
					$cmdValid = false;
				}
			}
			
			if ($cmdValid) {
				if (!isset($cmdData['actions']) || empty($cmdData['actions'])) {
					$valid = false;
					$errors[] = "Module: ".$moduleId." Cmd: ".$cmdId." - no actions found...";
				} else {
					try {
						$obj = new $cmdClass();
						foreach ($cmdData['actions'] AS $actionId => $actionData) {
							$actionMethod = $router->getActionMethod($actionId);
							if (!method_exists($obj, $actionMethod)) {
								$valid = false;
								$errors[] = $moduleId."/".$cmdId."/".$actionId." - action method missing: ".$actionMethod;
							}
						}
						unset($obj);
					} catch (\Exception $e) {
						die("werf");
						$valid = false;
						$errors[] = $moduleId."/".$cmdId."/".$actionId." - exception: ".$e->getMessage();
					}
				}
			}			
		}
	}
	
}




# checking template files

foreach ($modules AS $moduleId => $moduleData) {
	if (isset($moduleData['template'])) {
		$moduleTemplate = $template->getModulesPath() . $moduleData['template'];
		if (!$template->templateFileExists($moduleTemplate)) {
			$valid = false;
			$errors[] = $moduleId."// - custom template file missing: ".$moduleTemplate." (check modules config)";
		}
	} else {
		$moduleTemplate = $template->getModulesPath() . ProjectConstants::MODULES_DEFAULT_TEMPLATE;
		if (!$template->templateFileExists($moduleTemplate)) {
			$valid = false;
			$errors[] = $moduleId."// - default template file missing: ".$moduleTemplate;
		}
	}
	
	foreach ($moduleData['cmds'] AS $cmdId => $cmdData) {
		if (isset($cmdData['template'])) {
			$cmdTemplate = $template->getModulesPath() . $cmdData['template'];
			if (!$template->templateFileExists($cmdTemplate)) {
				$valid = false;
				$errors[] = $moduleId."/".$cmdId."/"." - custom template file missing: ".$cmdTemplate." (check modules config)";
			}
		} else {
			$cmdTemplate = $template->getModulesPath() . ProjectConstants::CMDS_DEFAULT_TEMPLATE;
			if (!$template->templateFileExists($cmdTemplate)) {
				$valid = false;
				$errors[] = $moduleId."/".$cmdId."/"." - default template file missing: ".$cmdTemplate;
			}
		}
		
		// actions
		foreach ($cmdData['actions'] AS $actionId => $actionData) {
			$actionTemplate = getActionTemplatePath($template, $moduleId, $cmdId, $actionId);
			if (!$template->templateFileExists($actionTemplate)) {
				$valid = false;
				$errors[] = $moduleId."/".$cmdId."/".$actionId." - action template file missing: ".$actionTemplate;
			}
		}
	}
}

# check result

if ($valid) {
	echo "\n\n Config check: OK";
} else {
	echo "\n\n Config check FAILED";
	echo "\n\n ".count($errors). " Errors found: ";
	foreach ($errors AS $err) {
		echo "\n\n - ".$err;
	}
}


echo "\n\n\n";


