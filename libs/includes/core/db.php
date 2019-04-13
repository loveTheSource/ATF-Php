<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Exceptions;

use ATFApp\Core;

/**
 * Core Db
 * manages db PDO connections
 * creates ATFApp\Core\PdoDb objects
 * 
 * the connections are saved in a global array to prevent
 * multiple connections to a single connection id (host/db)
 *
 * refer to db_config.php for connection(id)s
 */
class Db {
	
	// db connections
	private static $connections = [];
	
	// private to force singleton
	private function __construct() { }

	public static function getConnection($connectionId) {
		if (!array_key_exists($connectionId, self::$connections) || is_null(self::$connections[$connectionId])) {
			self::$connections[$connectionId] = self::createConnection($connectionId);
		}
		return self::$connections[$connectionId];
	}
	
	public static function getAllConnections() {
		return self::$connections;
	}
	
	/**
	 * create database connection using PHP PDO
	 * 
	 * @param string $connectionId
	 * @throws DbException
	 * @return \ATFApp\Core\PdoDb
	 */
	private static function createConnection($connectionId) {
		try {
			// connection config
			$connConfig = BasicFunctions::getConfig('db_config', $connectionId);
				
			// check config
			if (is_null($connConfig) || !is_array($connConfig)) throw new Exceptions\Db("invalid connection id - not in config: " . $connectionId);
			if (!isset($connConfig['type'])) throw new Exceptions\Db("invalid connection config - type missing: " . $connectionId);
			if (!isset($connConfig['host'])) throw new Exceptions\Db("invalid connection config - host missing: " . $connectionId);
			if (!isset($connConfig['db'])) throw new Exceptions\Db("invalid connection config - db missing: " . $connectionId);
			if ($connConfig['type'] != 'sqlite') {
				// sqlite needs neither user nor password
				if (!isset($connConfig['user'])) throw new Exceptions\Db("invalid connection config - user missing: " . $connectionId);
				if (!isset($connConfig['pass'])) throw new Exceptions\Db("invalid connection config - pass missing: " . $connectionId);
			}
			
			switch ($connConfig['type']) {
				case 'mysql':
					$dsn = 'mysql:host=' . $connConfig['host'] . ';dbname=' . $connConfig['db'];
					$options = [
						\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
					];
					if (BasicFunctions::useProfiler()) {
						// PdoProfiler: PdoDb with profiler
						$dbh = new Core\Db\PdoProfiler($dsn, $connConfig['user'], $connConfig['pass'], $options);
					} else {
						$dbh = new Core\Db\PdoDb($dsn, $connConfig['user'], $connConfig['pass'], $options);
					}
					break;
					
				case 'pgsql':
					$dsn = 'pgsql:host=' . $connConfig['host'] . ';dbname=' . $connConfig['db'];
					$options = ";options='-c client_encoding=utf8'";
					if (BasicFunctions::useProfiler()) {
						// PdoProfiler: PdoDb with profiler
						$dbh = new Core\Db\PdoProfiler($dsn . $options, $connConfig['user'], $connConfig['pass']);
					} else {
						$dbh = new Core\Db\PdoDb($dsn . $options, $connConfig['user'], $connConfig['pass']);
					}
					break;
					
				case 'sqlite':
					// TODO untested!!!
					// resolves to either 'sqlite:/path/to/db/file' or 'sqlite::memory'
					$dsn = 'sqlite:' . $connConfig['host'];
					$dbh = new Core\Db\PdoDb($dsn, null, null, []);
					break;
					
				default:
					throw new Exceptions\Db("invalid connection config - type invalid: " . $connectionId);
			}
			
			return $dbh;
		} catch (\Exception $e) {
			throw $e;
		}
	}

}