<?php

namespace ATFApp;

require_once 'constants.default.php';

class ProjectConstants extends ConstantsDefaults {
	
	// project name
	const PROJECT_NAME = "ATF - dev";
	
	// default language
	const DEFAULT_LANG = "de";
	
	// profiler
	const PROFILER_ENABLED = true;
	
	// global models query cache
	const MODELS_QUERY_CACHE = true;

	// logging for certain db queries
	const DB_LOGGING = true;
	
	// maintenance mode enabled
	const MAINTENANCE_MODE = false;
}
