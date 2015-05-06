<?php

namespace ATFApp;

/**
 * default project constants
 * 
 * DO NOT MAKE ANY CHANGES HERE
 * change in ProjectConstants instead
 */
abstract class ConstantsDefaults {

	
	# +++++++++++++++++++ main settings +++++++++++++++++++
	
	// project name
	const PROJECT_NAME = "New ATF Project";
	
	// maintenance mode enabled
	const MAINTENANCE_MODE = false;
	
	// profiler
	const PROFILER_ENABLED = false;
	
	// global models query cache
	const MODELS_QUERY_CACHE = false;
	
	// default language
	const DEFAULT_LANG = "en";
	
	// document title separator
	const TITLE_SEPARATOR = " | ";
	
	// default template for modules
	const MODULES_DEFAULT_TEMPLATE = "default_module.phtml";
	
	// default template for cmds
	const CMDS_DEFAULT_TEMPLATE = "default_cmd.phtml";
	
	// template engine (php/smarty/..)
	const SYSTEM_TEMPLATE_ENGINE = "php";
	
	// default database connection id (db_config.php)
	const DB_DEFAULT_CONNECTION = "default";
	
	
	
	# +++++++++++++++++++ special routes +++++++++++++++++++
	
	// default route
	const DEFAULT_MODULE = "index";		// default module
	const DEFAULT_CMD = "index";		// default cmd
	const DEFAULT_ACTION = "index";		// default action
	
	// auth module - forwarded to if access denied AND not logged in
	const MODULE_AUTH = "auth";
	
	// 403 module - used if access denied
	const MODULE_403 = "403";
	
	// 404 module - used if route not found
	const MODULE_404 = "404";
	
	
	
	# +++++++++++++++++++ session keys +++++++++++++++++++
	
	// session key for module
	const KEY_SESSION_MODULE = "project_module";
	
	// session key for cmd
	const KEY_SESSION_CMD = "project_cmd";

	// session key for action
	const KEY_SESSION_ACTION = "project_action";
	
	// session key for skin
	const KEY_SESSION_SKIN = "project_skin";
	
	
	// system messages (if stored in session)
	const KEY_SESSION_SYSTEM_MSG = "session_system_msg";
	
	// session key for token
	const KEY_SESSION_TOKEN = "session_token";
	

	// session key login status
	const KEY_SESSION_LOGGED_IN = "user_logged_in";
	
	// session key for user id
	const KEY_SESSION_USER_ID = "user_id";
	
	// session key for user login (username, email, ...)
	const KEY_SESSION_USER_LOGIN = "user_login";
	
	// session key for  user group ids (array)
	const KEY_SESSION_USER_GROUPS = "user_groups";

	// session key user language
	const KEY_SESSION_USER_LANG = "user_lang";
	
	// session key for redirect to url on auth success
	const KEY_SESSION_REDIRECT_ON_AUTH = "redirect_on_auth";
	
	// TODO either the following or the ones above should be removed.. just testing the user model
	const KEY_SESSION_MODEL_USER = "model_user";
	
	
	
	# +++++++++++++++++++ globals keys +++++++++++++++++++
	
	// globals key for system messages
	const KEY_GLOBAL_SYSTEM_MSG = "system_msg";
	
	// globals key for models query cache
	const KEY_GLOBALS_MODELS_QUERY_CACHE = "global_models_query_cache";
	
	// globals counter key for models query cache retrievals
	const KEY_GLOBALS_MODELS_QUERY_CACHE_COUNT = "global_models_query_cache_count";
	
	/**
	 * session vs globals: 
	 * 
	 * at the beginning the vars module/cmd/action in session and globals are equal 
	 * (depending on request)
	 * 
	 * the globals may be overwritten (e.g. in case of forwarding)
	 */ 	
	// globals key for current module
	const KEY_GLOBAL_MODULE = "project_module";
	
	// globals key for current cmd
	const KEY_GLOBAL_CMD = "project_cmd";
	
	// globals key for current action
	const KEY_GLOBAL_ACTION = "project_action";
	
	// globals key forwarding status (boolean)
	const KEY_GLOBAL_FORWARDING = "forwarding_active";
	
	// globals key count forwardings
	const KEY_GLOBAL_FORWARDING_COUNT = "forwards_count";
	
	
}