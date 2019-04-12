<?php

/**
 * application wide constants
 * (environment and paths)
 */

# application environment
if (!defined("ENVIRONMENT")) {
	if (isset($_SERVER['ENVIRONMENT']) && in_array($_SERVER['ENVIRONMENT'], ['debug', 'staging', 'live'])) {
		define("ENVIRONMENT", $_SERVER['ENVIRONMENT']);
	} else {
		define("ENVIRONMENT", 'live'); // default if webserver/vhost has no environment assigned
	}
}

# admin emails (required for exception handling)
if (!defined("ADMIN_EMAILS")) define("ADMIN_EMAILS", serialize([]));


# main application path
if (!defined("MAIN_PATH")) define("MAIN_PATH", realpath(dirname( getcwd() )) . DIRECTORY_SEPARATOR);
# main folders
if (!defined("LIBS_PATH")) define("LIBS_PATH", MAIN_PATH . "libs" . DIRECTORY_SEPARATOR);
if (!defined("CONFIG_PATH")) define("CONFIG_PATH", MAIN_PATH . "config" . DIRECTORY_SEPARATOR);
if (!defined("HTDOCS_PATH")) define("HTDOCS_PATH", MAIN_PATH . "www" . DIRECTORY_SEPARATOR);
if (!defined("TEMPLATES_PATH")) define("TEMPLATES_PATH", MAIN_PATH . "templates" . DIRECTORY_SEPARATOR);
if (!defined("TEMP_PATH")) define("TEMP_PATH", MAIN_PATH . "temp" . DIRECTORY_SEPARATOR);
# subfolders
if (!defined("INCLUDES_PATH")) define("INCLUDES_PATH", LIBS_PATH . "includes" . DIRECTORY_SEPARATOR);
if (!defined("LANG_PATH")) define("LANG_PATH", LIBS_PATH . "lang" . DIRECTORY_SEPARATOR);
if (!defined("CONTROLLER_PATH")) define("MODULES_PATH", LIBS_PATH . "controller" . DIRECTORY_SEPARATOR);
if (!defined("EXCEPTIONS_PATH")) define("EXCEPTIONS_PATH", LIBS_PATH . "exceptions" . DIRECTORY_SEPARATOR);
if (!defined("MODELS_PATH")) define("MODELS_PATH", LIBS_PATH . "models" . DIRECTORY_SEPARATOR);
if (!defined("APP_PATH")) define("APP_PATH", LIBS_PATH . "app" . DIRECTORY_SEPARATOR);
# core path
if (!defined("CORE_PATH")) define("CORE_PATH", INCLUDES_PATH . "core" . DIRECTORY_SEPARATOR);
# helper path
if (!defined("HELPER_PATH")) define("HELPER_PATH", INCLUDES_PATH . "helper" . DIRECTORY_SEPARATOR);

# web folders
if (!defined("WEBFOLDER")) define("WEBFOLDER", "/");
if (!defined("WEBFOLDER_CSS")) define("WEBFOLDER_CSS", WEBFOLDER . "css/");
if (!defined("WEBFOLDER_JS")) define("WEBFOLDER_JS", WEBFOLDER . "js/");
if (!defined("WEBFOLDER_IMAGES")) define("WEBFOLDER_IMAGES", WEBFOLDER . "images/");



