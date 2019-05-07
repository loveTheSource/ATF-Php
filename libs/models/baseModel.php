<?php

namespace ATFApp\Models;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

abstract class BaseModel {
	
	public function __set($key, $value) {
		if (array_key_exists($key, $this->tableColumns)) {
			$this->$key = $value;
		} else {
			throw new Exceptions\Db('Column "' . $key . '" is invalid in table: ' . $this->table);
		}
	}

	/**
	 * get from query cache
	 * 
	 * @param string $key
	 * @return array
	 */
	public function getFromQueryCache($key) {
		$cacheData = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE);
		if (!is_array($cacheData)) $cacheData = [];
		
		if (array_key_exists($key, $cacheData)) {
			$counter = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE_COUNT);
			if (is_null($counter)) {
				$counter = 1;
			} else {
				$counter++;
			}
			Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE_COUNT, $counter);
			return $cacheData[$key];
		}
		return null;
	}
	
	/**
	 * save to query cache
	 * 
	 * @param string $key
	 * @param unknown $data
	 */
	public function saveToQueryCache($key, $data) {
		$cacheData = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE);
		if (!is_array($cacheData)) $cacheData = [];
		$cacheData[$key] = $data;
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE, $cacheData);
	}
	
	/**
	 * current model table
	 * 
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}
	
	/**
	 * primary key columns
	 * 
	 * @return array
	 */
	public function getPrimaryKeyColumns() {
		return $this->tablePrimaryKeys;
	}
	
	public function getUpdateColumns() {
		$updateCols = [];
		foreach ($this->tableColumns as $col => $info) {
			$updateCols[] = $col;
		}
		return $updateCols;
	}
	
	public function fitValueToColumn($col, $value) {
		$colSettings = $this->tableColumns[$col];
		$fixed = null;

		if (is_null($value) && isset($colSettings['null']) && $colSettings['null'] === true) {
			$fixed = null;
		} else {
			switch ($colSettings['type']) {
				case 'string':
					$fixed = substr((string)$value, 0, $colSettings['length']);
					break;

				case 'int':
					$fixed = (int)(substr($value, 0, $colSettings['length']));
					break;

				case 'double':
					$fixed = (double)(substr($value, 0, $colSettings['length']));
					break;

				case 'timestamp':
					if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $value)) {
						$fixed = $value;
					} elseif (in_array(strtoupper($value), ['NOW()', 'CURRENT_TIMESTAMP()'])) {
						$fixed = strtoupper($value);
					}
					break;

				case 'text':	// text column
				default: 	// plus all others
					$fixed = $value;
			}
		}

		return $fixed;
	}

	/**
	 * get pdo db object
	 *
	 * @return \ATFApp\Core\Db\PdoDb
	 */
	public function getDb() {
		return Core\Factory::getDbObj($this->dbConnection);
	}

	/**
	 * return type of db connection
	 * - mysql
	 * - pgsql
	 * - sqlite
	 * 
	 * @return string|false
	 */
	public function getDbConnectionType() {
		$dbConf = BasicFunctions::getConfig('db');
		$connectionId = $this->dbConnection;

		if (isset($dbConf[$connectionId])) {
			if (isset($dbConf[$connectionId]['type'])) {
				return $dbConf[$connectionId]['type'];
			}
		}

		return false;
	}
}