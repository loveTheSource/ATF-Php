<?php

/**
 * 
 * modules DEFAULT config
 * 
 * do not change anything here. those modules are mandatory
 * 
 * if authentication is not required just comment out the '/auth' route
 * and remember to remove all 'access' options in route configs
 * 
 */

/**
 * modules config
 *
 * IMPORTANT: 
 * access to subroutes is only granted if access to the parent route is granted too!
 * 
 * 
 * usage (minimal):
 *
 * $routesConfig['/'] = [				// url path
 * 	'module' => 'index',				// module is also the folder within 'controller'
 *  'controller' => 'IndexController',	// class name
 *  'action' => 'index',				// action name (without 'Action')
 *  'template' => 'index.phtml'			// template file
 * ];
 *
 * usage (including optional values):
 *
 * $routesConfig['/full-example'] = [
 *	'module' => 'demo',
 *	'controller' => 'DemoController',
 *	'action' => 'index',
 *	'template' => 'index.phtml',
 *	'subroutes' => [
 *		'/restricted' => [
 *			'module' => 'demo',
 *			'controller' => 'DemoController',
 *			'action' => 'restricted',
 *			'template' => 'restricted.phtml',
 *			'access' => [
 *				'groups' => [],
 *				'users' => []
 *			]
 *		],
 *		'/non-restricted' => [
 *			'module' => 'demo',
 *			'controller' => 'DemoController',
 *			'action' => 'nonrestricted',
 *			'template' => 'non-restricted.phtml',
 *		]
 *	 ]
 * ];
 *
 */

$routesConfig = [];

/**
 * index controller
 * 
 * route /
 * 
 * add subroutes in routes_config.php
 * $routesConfig['/']['subroutes'] = .....
 */ 
$routesConfig['/'] = [
	'module' => 'index',
	'controller' => 'IndexController',
	'action' => 'index',
	'template' => 'index.phtml',
];


/**
 * auth controller
 * 
 * routes: 	/auth
 * 			/auth/login
 * 			/auth/logout
 * 
 * add subroutes in routes_config.php
 * $routesConfig['/auth']['subroutes'][] = [...subroute...]
*/
$routesConfig['/auth'] = [
	'module' => 'auth',
	'controller' => 'AuthController',
	'action' => 'index',
	'template' => 'index.phtml',
	'subroutes' => [
		'/login' => [
			'module' => 'auth',
			'controller' => 'AuthController',
			'action' => 'login',
			'template' => 'login.phtml',
		],
		'/logout' => [
			'module' => 'auth',
			'controller' => 'AuthController',
			'action' => 'logout',
			'template' => 'login.phtml',
		]
	]
];


/**
 * 403 Forbidden
 * 
 * route /403
 */
$routesConfig['/403'] = [
	'module' => 'errors',
	'controller' => 'ErrorController',
	'action' => 'get403',
	'template' => '403.phtml',
];


/**
 * 404 Not Found
 * 
 * route /404
 */
$routesConfig['/404'] = [
	'module' => 'errors',
	'controller' => 'ErrorController',
	'action' => 'get404',
	'template' => '404.phtml',
];


return $routesConfig;