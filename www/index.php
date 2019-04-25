<?php 

namespace ATFApp;

use ATFApp\Exceptions as Exceptions;

setlocale(LC_ALL,'en_US.UTF-8');

$cwd = realpath('../') . DIRECTORY_SEPARATOR;

// main config file
require_once $cwd . 'config/base/main_config.php';
// fallback version
require_once $cwd . 'config/base/main_config.default.php';

// error handling
require_once EXCEPTIONS_PATH . 'ExceptionHandler.php';
\ATFApp\Exceptions\ExceptionHandler::setEmailRecipients(unserialize(ADMIN_EMAILS));

// main project
require_once LIBS_PATH . 'project.php';

try {
	// create project object and run
	$project = new ATFProject();
	$project->init();
	$project->run();
	exit();
} catch (\Exception $e) {
	Exceptions\ExceptionHandler::handle($e);
	
	// display error message in live environment
	if (defined("ENVIRONMENT") && ENVIRONMENT == "live") {
		// output in case of error
		$errorFile = "error.html";
		if (!headers_sent()) {
			// redirect to error page
			$errorFile = WEBFOLDER . $errorFile;
			header("Location: $errorFile");
		} elseif (is_file(LIBS_PATH . $errorFile) && is_readable(LIBS_PATH . $errorFile) && function_exists('readfile')) {
			// load and display error page
			@readfile(LIBS_PATH . $errorFile);
			exit();
		} else {
			// simply die
			die("Sorry, an error occured");
		}
	}
}
