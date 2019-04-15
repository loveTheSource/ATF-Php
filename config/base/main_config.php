<?php

/**
 * application wide constants
 * (environment and paths)
 */

# application environment
if (isset($_SERVER['ENVIRONMENT']) && in_array($_SERVER['ENVIRONMENT'], ['debug', 'staging', 'production'])) {
	define("ENVIRONMENT", $_SERVER['ENVIRONMENT']);
} else {
	define("ENVIRONMENT", 'production'); // default if webserver/vhost has no env assigned
}



# admin emails (required for exception handling)
define("ADMIN_EMAILS", serialize(['admin@cre8.info']));


# main application path
define("MAIN_PATH", realpath(dirname( getcwd() )) . DIRECTORY_SEPARATOR);
# main folders
define("LIBS_PATH", MAIN_PATH . "libs" . DIRECTORY_SEPARATOR);
define("CONFIG_PATH", MAIN_PATH . "config" . DIRECTORY_SEPARATOR);
define("HTDOCS_PATH", MAIN_PATH . "www" . DIRECTORY_SEPARATOR);
define("TEMPLATES_PATH", MAIN_PATH . "templates" . DIRECTORY_SEPARATOR);
define("TEMP_PATH", MAIN_PATH . "temp" . DIRECTORY_SEPARATOR);
# subfolders
define("INCLUDES_PATH", LIBS_PATH . "includes" . DIRECTORY_SEPARATOR);
define("LANG_PATH", LIBS_PATH . "lang" . DIRECTORY_SEPARATOR);
define("CONTROLLER_PATH", LIBS_PATH . "controller" . DIRECTORY_SEPARATOR);
define("EXCEPTIONS_PATH", LIBS_PATH . "exceptions" . DIRECTORY_SEPARATOR);
define("MODELS_PATH", LIBS_PATH . "models" . DIRECTORY_SEPARATOR);
define("APP_PATH", LIBS_PATH . "app" . DIRECTORY_SEPARATOR);
# core path 
define("CORE_PATH", INCLUDES_PATH . "core" . DIRECTORY_SEPARATOR);
# helper path
define("HELPER_PATH", INCLUDES_PATH . "helper" . DIRECTORY_SEPARATOR);

# web folders
define("WEBFOLDER", "/");
define("WEBFOLDER_CSS", WEBFOLDER . "css/");
define("WEBFOLDER_JS", WEBFOLDER . "js/");
define("WEBFOLDER_IMAGES", WEBFOLDER . "images/");


