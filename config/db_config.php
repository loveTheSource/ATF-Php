<?php

/**
 * 
 * database config
 * 
 * $dbConfig['default'] = [
 *	'type' => 'mysql',				// pdo type
 *	'host' => 'localhost',			// ip / host
 * 	'user' => 'db_user',			// username
 *	'pass' => 'password',			// password
 *	'db'   => 'db_name'				// database
 * ];
 * 
 * currently supported types:
 * mysql, pgsql, sqlite
 * 
 * for type sqlite:
 * - use config key 'host' to define database file or set to ':memory' for in memory
 * - username and password are not required
 * 
 */

$dbConfig = [];

$dbConfig['default'] = [
	// 'type' => 'mysql',
	// 'host' => 'db',
	// 'user' => 'root',
	// 'pass' => 'pass123',
	// 'db' => 'atfphp'
	'type' => 'pgsql',
	'host' => 'postgres',
	'user' => 'user',
	'pass' => 'pass123',
	'db' => 'atfphp'
];

return $dbConfig;