<?php 

namespace ATFApp;

use ATFApp\Exceptions as Exceptions;

// change directory
// chdir ('../');

$cwd = realpath(dirname( getcwd() )) . DIRECTORY_SEPARATOR;
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
	
	// display error message in production environment
	if (defined("ENVIRONMENT") && ENVIRONMENT == "production") {
		// output in case of error
		$errorFile = "error.html";
		if (!headers_sent()) {
			// redirect to error page
			$errorFile = WEBFOLDER . $errorFile;
			header("Location: $errorFile");
		} elseif (is_file($errorFile) && is_readable($errorFile) && function_exists('readfile')) {
			// load and display error page
			@readfile(HTDOCS_PATH . $errorFile);
			exit();
		} else {
			// simply die
			die("Sorry, an error occured");
		}
	}
}
