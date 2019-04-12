<?php

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


// test module
$routesConfig['/test'] = [
	'module' => 'test',
	'controller' => 'TestController',
	'action' => 'index',
	'template' => 'index.phtml',
	'subroutes' => [
		'/test' => [
			'module' => 'test',
			'controller' => 'TestController',
			'action' => 'test',
			'template' => 'test.phtml',
			'access' => []
		]
	]
];


return $routesConfig;