<?php

$projectConfig = [];


$projectConfig['project_url'] = "http://127.0.0.1" . DIRECTORY_SEPARATOR;


// default skin
$projectConfig['default_skin'] = "default";


// skins 
$projectConfig['skins'] = [];
$projectConfig['skins']['default'] = [];
$projectConfig['skins']['default']['enabled'] = 1;
$projectConfig['skins']['default']['folder'] = "default";


// maximum forwardings
$projectConfig['forwarding_limit'] = 5;


// return config
return $projectConfig;