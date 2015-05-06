<?php

namespace ATFApp\Models;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

abstract class BaseModel {
	
	public function __set($key, $value) {
		if (property_exists($this, $key)) {
			$this->$key = $value;
		} else {
			$map = $this->getColumnMappings();
			if (array_key_exists($key, $map)) {
				$key = $map[$key];
				$this->$key = $value;
			}
		}
	}
	
	/**
	 * get from query cache
	 * @param string $key
	 * @return array
	 */
	public function getFromQueryCache($key) {
		$cacheData = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE);
		if (!is_array($cacheData)) $cacheData = array();
		
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
		if (!is_array($cacheData)) $cacheData = array();
		$cacheData[$key] = $data;
		Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE, $cacheData);
	}
	
	public function getColumnMappings() {
		if (isset($this->columnMappings)) {
			return $this->columnMappings;
		} else {
			return array();
		}		
	}
	
	public function getTable() {
		return $this->table;
	}
	
	public function getPrimaryKeyColumns() {
		return $this->primaryKeyColumns;
	}
	
	public function getUpdateColumns() {
		return $this->updateColumns;
	}
	
	/**
	 * get pdo db object
	 *
	 * @return \ATFApp\Core\PdoDb
	 */
	public function getDb() {
		return Core\Factory::getDbObj();
	}
}