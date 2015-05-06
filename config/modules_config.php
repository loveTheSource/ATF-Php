<?php

/**
 * modules config
 *
 * - template paths must be relative to the 'templates/skin/modules/' folder
 * 
 * 
 * usage (minimal):
 *
 * $modulesConfig['index'] = array(				// module index
 * 	'cmds' => array(
 * 		'index' => array(						// cmd index
 * 			'actions' => array(
 * 				'index' => array()				// action index
 *			)
 * 		)
 * 	)
 * );
 *
 * usage (including optional values):
 *
 * $moduleConfig['module1'] = array(			// module name
 * 	'cmds' => array(							// cmds in module
 * 		'cmd1' => array(						// example cmd
 * 			'actions' => array(					// actions in cmd
 * 				'action1' => array(				// example action
 * 					'lang_key' => 'lang_key'	// key in the routing langPack
 * 				),
 * 				'action2' => array(...)			// another action inside the cmd
 * 			),
 * 			'template' => 'path/to/cmd.phtml',	// template to use for cmd (default_cmd.phtml if not exists)
 * 			'lang_key' => 'cmd1_lang_key',		// key in the routing langPack
 * 			'access' => array(					// authentication required for cmd once the key 'access' exists
 * 												// to enter this cmd user must meet module access requirement as well
 * 												// if this array is empty, all users are allowed, once logged in
 * 				'groups' => array(2, 5)			// allowed group ids
 * 				'users' => array(123, 125)		// allowed user ids
 * 			),
 * 		),
 * 		'cmd2' => array(...)					// another cmd inside the module
 * 	),
 *  'template' => 'path/to/module.ptml',		// template to use for module (default_module.phtml if not exists)
 * 	'lang_key' => 'module1_translation_key',	// key in the routing langPack
 * 	'access' => array(							// authentication required for module once the key 'access' exists
 * 												// if this array is empty, all users are allowed, once logged in
 * 		'group' => array(1, 2, 5),				// allowed group ids
 * 		'users' => array(123, 207, 521)			// allowed user ids
 * 	)
 * );
 *
 */

$modulesConfig = array();


// test module
$modulesConfig['test'] = array(
	'cmds' => array(
		'index' => array(
			'template' => 'auth/cmds/index/cmd_index.phtml',
			'actions' => array(
				'index' => array(),
				'test' => array(),
				'name' => array(),
				'namespace' => array()
			)
		),
		'restricted' => array(
			'actions' => array(
				'index' => array()
			),
			'access' => array(
				'users' => array(),
				'groups' => array(2)
			)
		)
	)
);

return $modulesConfig;