<?php

/**
 * 
 * database config
 * 
 * $dbConfig['default'] = array(
 *	'type' => 'mysql',				// pdo type
 *	'host' => 'localhost',			// ip / host
 * 	'user' => 'db_user',			// username
 *	'pass' => 'password',			// password
 *	'db'   => 'db_name'				// database
 * );
 * 
 * currently supported types:
 * mysql, pgsql, sqlite
 * 
 * for type sqlite:
 * - use config key 'host' to define database file or set to ':memory' for in memory
 * - username and password are not required
 * 
 */

$dbConfig = array();

$dbConfig['default'] = array(
	'type' => 'mysql',
	'host' => 'localhost',
	'user' => 'root',
	'pass' => 'abc123',
	'db' => 'webframework'
);

return $dbConfig;