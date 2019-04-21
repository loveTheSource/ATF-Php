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
	
	// logging for certain db queries
	const DB_LOGGING = false;
	
	// default language
	const DEFAULT_LANG = "en";
	
	// document title separator
	const TITLE_SEPARATOR = " | ";
	
	// template engine (php/smarty/..)
	const SYSTEM_TEMPLATE_ENGINE = "php";
	
	// default database connection id (db_config.php)
	const DB_DEFAULT_CONNECTION = "default";
	
	// db connection type keys
	const DB_CONNECTION_TYPE_MYSQL = "mysql";
	const DB_CONNECTION_TYPE_PGSQL = "pgsql";
	const DB_CONNECTION_TYPE_SQLITE = "sqlite";

	// show environment in badge on page top
	const ENVIRONMENT_BADGE = true;

	// force CSRF token validation
	const CSRF_FORCE_VALIDATION = true;
	
	// CSRF token expiry (seconds)
	const CSRF_TOKENS_EXPIRY = 60 * 60 * 3;  // 3 hrs
	
	// CSRF token param name
	const CSRF_POST_PARAM = "_CSRF";
	
	# +++++++++++++++++++ special routes +++++++++++++++++++
	
	// default route
	const ROUTE_DEFAULT = "/";		// default route
	
	// auth module - forwarded to if access denied AND not logged in
	const ROUTE_AUTH = "/auth";
	
	// 403 module - used if access denied
	const ROUTE_403 = "/403";
	
	// 404 module - used if route not found
	const ROUTE_404 = "/404";
	
	
	
	# +++++++++++++++++++ session keys +++++++++++++++++++
	
	// session key CSRF tokens
	const KEY_SESSION_CSRF_TOKENS = "csrf_tokens";

	// session key for route
	const KEY_SESSION_ROUTE = "session_route";
	
	// session key for module
	const KEY_SESSION_MODULE = "session_module";
	
	// session key for skin
	const KEY_SESSION_SKIN = "project_skin";
	
	// system messages (if stored in session)
	const KEY_SESSION_SYSTEM_MSG = "session_system_msg";
	
	// session key for token
	const KEY_SESSION_TOKEN = "session_token";
	
	// session key for redirect url
	const KEY_SESSION_REDIRECT_ON_AUTH = "redirect_on_auth";
	
	// session key for language
	const KEY_SESSION_LANG = "session_lang";
	
	// session key login status
	const KEY_SESSION_LOGGED_IN = "user_logged_in";
	
	// session key for user id
	const KEY_SESSION_USER_ID = "user_id";
	
	// session key for  user group ids (array)
	const KEY_SESSION_USER_GROUPS = "user_groups";
	
	
	
	# +++++++++++++++++++ globals keys +++++++++++++++++++
	
	// current used csrf token
	const KEY_GLOBAL_USED_CSRF_TOKEN = "used_csrf_token";

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
	// globals key for current route
	const KEY_GLOBAL_ROUTE = "global_route";
	
	// globals key for current module
	const KEY_GLOBAL_MODULE = "global_module";
	
	// globals key forwarding status (boolean)
	const KEY_GLOBAL_FORWARDING = "global_forwarding_active";
	
	// globals key count forwardings
	const KEY_GLOBAL_FORWARDING_COUNT = "global_forwards_count";

	// globals key for the route params
	const KEY_GLOBAL_ROUTE_PARAMS = "global_route_params";
	
}