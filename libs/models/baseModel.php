<?php

namespace ATFApp\Models;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

abstract class BaseModel {
	
	public function __set($key, $value) {
		if (in_array($key, $this->tableColumns)) {
			$this->$key = $value;
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
		foreach ($this->tableColumns as $col) {
			if (is_array($this->tableColumnsProtected) && !in_array($col, $this->tableColumnsProtected)) {
				if (property_exists($this, $col)) {
					$updateCols[] = $col;
				}
			}
		}
		return $updateCols;
	}
	
	/**
	 * get pdo db object
	 *
	 * @return \ATFApp\Core\Db\PdoDb
	 */
	public function getDb() {
		return Core\Factory::getDbObj($this->dbConnection);
	}
}