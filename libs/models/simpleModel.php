<?php

namespace ATFApp\Models;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Exceptions;

use ATFApp\Helper;
use ATFApp\Core;
use ATFApp\Core\Includer;

require_once 'baseModel.php';

abstract class SimpleModel extends BaseModel {
	
	protected $dbConnection = ProjectConstants::DB_DEFAULT_CONNECTION;
	protected $tableColumnsProtected = [];
	protected $tableForeignKeys = [];
	protected $tableRelations = [];

	public function __construct() {	}
	
	/**
	 * update database entry (only given columns)
	 * 
	 * @param array $columns
	 * @throws DbException
	 * @return boolean
	 */
	public function update(Array $columns=[]) {
		if (empty($columns)) {
			$columns = $this->getUpdateColumns();
		}
		$updateCols = $this->getUpdateColumns();
		$table = $this->getTable();
		
		$db = $this->getDb();
	
		$query = "UPDATE " . $table . " SET ";
		
		$c = 0;
		$params = [];
		foreach ($columns AS $i => $col) {
			if (!in_array($col, $updateCols)) {
				throw new Exceptions\Db("cannot update column '" . var_export($col, true) . "' in table " . $table);
			} else {
				if ($c != 0) $query .= ", ";
				$colName = $col;
				$query .= " " . $colName . " = :" . $colName;
				$params[$colName] = $this->$col;
				$c++;
			}
		}
	
		$query .= " WHERE ";
		foreach ($this->getPrimaryKeyColumns() AS $i => $col) {
			if ($i != 0) $query .= " AND ";
			$query .= ' ' . $col . ' = :' . $col;
			$params[$col] = $this->$col;
		}
		$query .= "; ";
		
		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);
		$res = $statementHandler->execute($params);
		if ($res !== false) {
			return $res;
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $statementHandler->getErrors());
		}
	}
	

	/**
	 * insert new row into database
	 * 
	 * @throws Exceptions\Db
	 * @return boolean
	 */
	public function insert() {
		$columns = $this->getUpdateColumns();
		
		$query = 'INSERT INTO ' . $this->getTable();
		$queryCols = ' (';
		$queryVals = ' (';
		$queryData = [];
		
		$c = 0;
		foreach ($columns AS $i => $col) {
			if ($c != 0) {
				$queryCols .= ', ';
				$queryVals .= ', ';
			}
			$queryCols .= ' ' . $col;
			$queryData[$col] = $this->$col;
			$queryVals .= ' :' . $col;
			$c++;
		}
		
		$queryCols .= ') ';
		$queryVals .= ') ';
		
		$query .= $queryCols . ' VALUES ' . $queryVals . '; ';

		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);
		$res = $statementHandler->execute($queryData);

		if ($res !== false) {
			// try set the last insert id to the model as primary key
			$primaryKeyColumns = $this->getPrimaryKeyColumns();
			if (count($primaryKeyColumns) == 1) {
				$primaryKey = $primaryKeyColumns[0];
				$lastInsertId = $statementHandler->getLastInsertId();
				$this->$primaryKey = $lastInsertId;
			}
			return true;
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $statementHandler->getErrors());
		}
	}
	
	
	/** 
	 * delete row from db
	 * 
	 * @throws Exceptions\Db
	 * @return boolean
	 */
	public function delete() {
		$queryParams = [];
		
		$query = 'DELETE FROM ' . $this->getTable() . ' WHERE ';
		foreach ($this->getPrimaryKeyColumns() as $i => $col) {
			if ($i !== 0) {
				$query .= ' AND ';
			}
			$queryParams[$col] = $this->$col;
			$query .= ' ' . $col . ' = :'.$col;
		}
		$query .= ';';
		
		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);
		$res = $statementHandler->execute($queryParams);

		if ($res !== false) {
			return $res;
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $statementHandler->getErrors());
		}
	}
	
	
	/**
	 * select row from db and return model
	 * based on primary keys / values
	 *
	 * @param array $keys
	 * @param boolean $ignoreCache
	 * @throws Exceptions\Db
	 * @return boolean|SimpleModel actually false or an instance of the model
	 */
	public function selectByPrimaryKeys(Array $keys, $ignoreCache=false) {
		$primaryKeys = $this->getPrimaryKeyColumns();  // array of primary key columns

		$cacheKey = 'selByPrim_' . $this->getTable() . '_';
		foreach ($primaryKeys AS $k) {
			if (array_key_exists($k, $keys)) {
				$cacheKey .= $k . '=' . $keys[$k] . '-';
			} else {
				throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: key "' . $k . '" not found', null, null, $keys);
			}
		}
		if (!$ignoreCache && ProjectConstants::MODELS_QUERY_CACHE) {
			$cacheResult = $this->getFromQueryCache($cacheKey);
			if (!is_null($cacheResult)) {
				return $cacheResult;
			}
		}
		
		$modelClass = get_called_class();  // model class to use
	
		$query = "SELECT * FROM " . $this->getTable() . " WHERE ";
	
		$keysCounter = 0;
		$params = [];
		foreach ($primaryKeys AS $k) {
			// add primary key to where clause
			if ($keysCounter != 0) {
				$query .= 'AND ';
			}
			$query .= ' ' . $k . ' = :' . $k;
			$params[$k] = $keys[$k];
			$keysCounter++;
		}
		$query .= "; ";
		
		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);
		$result = $statementHandler->execute($params, 'cols');
		
		if ($result !== false) {
			$modelsList = $statementHandler->fetchAll(\PDO::FETCH_CLASS, $modelClass);
			
			$singleRow = false;
			if ($modelsList !== false) {
				if (count($modelsList) == 1) {
					// all ok
					$singleRow = $modelsList[0];
				} elseif (count($modelsList) > 1) {
					throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed (more than one result): ' . $query, null, null, $keys);
				} else {
					$singleRow = false;
				}
			}

			if (ProjectConstants::MODELS_QUERY_CACHE) {
				$this->saveToQueryCache($cacheKey, $singleRow);
			}

			return $singleRow;
			
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $statementHandler->getErrors());
		}
	}

	/**
	 * select rows from db by columns/values
	 * 
	 * @param array $columnValues
	 * @param $boolean $ignoreCache
	 * @throws Exception\Db
	 * @return boolean|array false or an array of models 
	 */
	public function selectByColumns(Array $columnValues, $ignoreCache=false) {
		$cacheKey = 'selByCols_' . $this->getTable() . '_';
		foreach ($columnValues AS $col => $val) {
			$cacheKey .= $col . '=' . $val . '-';
		}
		if (!$ignoreCache && ProjectConstants::MODELS_QUERY_CACHE) {
			$cacheResult = $this->getFromQueryCache($cacheKey);
			if (!is_null($cacheResult)) {
				return $cacheResult;
			}
		}
		
		$modelClass = get_called_class();  // model class to use
	
		$query = "SELECT * FROM " . $this->getTable() . " WHERE ";
	
		$keysCounter = 0;
		$params = [];
		foreach ($columnValues AS $col => $val) {
			if ($keysCounter !== 0) {
				$query .= ' AND ';
			}
			$query .= ' ' . $col . ' = :' . $col;
			$params[$col] = $val;
			$keysCounter++;
		}
		$query .= "; ";

		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);
		$result = $statementHandler->execute($params, 'cols');
		
		if ($result !== false) {
			$modelsList = $statementHandler->fetchAll(\PDO::FETCH_CLASS, $modelClass);
			
			if ($modelsList !== false) {
				if (count($modelsList) >= 1) {
					// all ok
				}
			} else {
				$modelsList = [];
			}
			if (ProjectConstants::MODELS_QUERY_CACHE) {
				$this->saveToQueryCache($cacheKey, $modelsList);
			}
			return $modelsList;
			
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $statementHandler->getErrors());
		}
	}

	/**
	 * select row(s) from db and return array of models
	 * based on given query
	 * 
	 * @param string $query
	 * @param array $params
	 * @param boolean $ignoreCache
	 * @throws Exceptions\Db
	 * @return boolean|SimpleModel actually an array of instances of the calling class that extends SimpleModel
	 */
	/*
	public function selectByQuery($query, $params=[], $ignoreCache=false) {
		$cacheKey = 'selByQuery_' . $this->getTable() . '_' . $query . '_' . var_export($params, true);
		if (!$ignoreCache && ProjectConstants::MODELS_QUERY_CACHE) {
			$cacheResult = $this->getFromQueryCache($cacheKey);
			if (!is_null($cacheResult)) {
				return $cacheResult;
			}
		}
		
		$modelClass = get_called_class();  // model class to use
		$db = $this->getDb();
		
		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);
		$result = $statementHandler->execute($params);
		if ($result !== false) {
			$modelsList = $statementHandler->fetchAll(\PDO::FETCH_CLASS, $modelClass);
			if ($modelsList !== false) {
				if (is_array($modelsList) && count($modelsList) >= 1) {
					// all ok
				} else {
					$modelsList = [];
				}
			} else {
				$modelsList = false;
			}
			
			if (ProjectConstants::MODELS_QUERY_CACHE) {
				$this->saveToQueryCache($cacheKey, $modelsList);
			}
			return $modelsList;
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $statementHandler->getErrors());
		}
	}
	*/

	/**
	 * read complete table and return array of models
	 * 
	 * @param integer $limit
	 * @param array $orderBy
	 * @return SimpleModel actually an array of models
	 */
	public function selectAll($start=0, $limit=null, $orderBy=[], $ignoreCache=false) {
		$cacheKey = 'selAll_' . $this->getTable() . '_' . $start . '-';
		$cacheKey .= (is_null($limit)) ? 'null' : $limit;
		foreach ($orderBy AS $col => $sort) {
			$cacheKey .= '_' . $col . '=' . $sort;
		}
		if (!$ignoreCache && ProjectConstants::MODELS_QUERY_CACHE) {
			$cacheResult = $this->getFromQueryCache($cacheKey);
			if (!is_null($cacheResult)) {
				return $cacheResult;
			}
		}
		
		$modelClass = get_called_class();  // model class to use
		
		// build query
		$query = "SELECT * FROM " . $this->getTable();
		
		if (is_array($orderBy) && count($orderBy) >= 1) {
			$query .= " ORDER BY ";
			$c = 0;
			foreach ($orderBy AS $col => $sort) {
				$col = trim(preg_replace('/[^\w\d\_\-]/si', '', $col)); //remove all illegal chars
				if (in_array($col, $this->tableColumns) && property_exists($this, $col)) {
					if ($c != 0) {
						$query .= ", ";
					}
					$sort = (strtolower($sort) == 'asc') ? 'ASC' : 'DESC';
					$query .= ' ' . $col . ' ' . $sort;
					$c++;
				}			
			}
		} else {
			$query .= " ORDER BY ";
			$keysCounter = 0;
			$primaryKeys = $this->getPrimaryKeyColumns();  // array of primary key columns
			foreach ($primaryKeys AS $k) {
				// add primary key to where clause
				if ($keysCounter != 0) {
					$query .= ', ';
				}
				$query .= ' ' . $k . ' ASC ';
				$keysCounter++;
			}
		}
		
		if (!is_null($limit)) {
			if ($this->getDbConnectionType() === ProjectConstants::DB_CONNECTION_TYPE_PGSQL) {
				$query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$start;
			} else {
				$query .= " LIMIT " . (int)$start . ", " . (int)$limit;
			}
		}
		
		$query .= "; ";
	
		// select from db		
		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);
		$result = $statementHandler->execute([], 'cols');
		if ($result !== false) {
			$modelsList = $statementHandler->fetchAll(\PDO::FETCH_CLASS, $modelClass);
			if ($modelsList !== false) {
				if (count($modelsList) >= 1) {
					// all ok
				}
			} else {
				$modelsList = [];
			}
			
			if (ProjectConstants::MODELS_QUERY_CACHE) {
				$this->saveToQueryCache($cacheKey, $modelsList);
			}
			return $modelsList;
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $statementHandler->getErrors());
		}
		
	}

	/**
	 * get data from foreign table
	 * using foreign key
	 * 
	 * @param string $foreignKey array key in model $tableForeignKeys
	 * @return array Models
	 */
	public function getForeignData($foreignKey) {
		if (!array_key_exists($foreignKey, $this->tableForeignKeys)) {
			throw new Exceptions\Db('foreign key "' . $foreignKey . '" unknown to table: ' . $this->table);
		}

		$foreignKeyConf = $this->tableForeignKeys[$foreignKey];
		$modelClass = '\ATFApp\Models\\' . $foreignKeyConf['model'];
		if (!class_exists($modelClass)) {
			throw new Exceptions\Db('model "' . $modelClass . '" unknown in foreign key "' . $foreignKey . '" - table: ' . $this->table);
		}
		$foreignModel = new $modelClass();
		$foreignData = $foreignModel->selectByColumns([$foreignKeyConf['remoteCol'] => $this->$foreignKey]);

		return $foreignData;
	}

	/**
	 * get data from related table
	 * 
	 * @param string $relationKey array key in model $tableRelations
	 * @return array Models
	 */
	public function getRelationData($relationKey) {
		if (!array_key_exists($relationKey, $this->tableRelations)) {
			throw new Exceptions\Db('relation key "' . $relationKey . '" unknown to table: ' . $this->table);
		}

		$relationConf = $this->tableRelations[$relationKey];
		$modelClass = '\ATFApp\Models\\' . $relationConf['model'];
		if (!class_exists($modelClass)) {
			throw new Exceptions\Db('model "' . $modelClass . '" unknown in foreign key "' . $relationKey . '" - table: ' . $this->table);
		}
		$foreignModel = new $modelClass();
		$sourceCol = $relationConf['sourceCol'];
		$foreignData = $foreignModel->selectByColumns([$relationConf['remoteCol'] => $this->$sourceCol]);

		return $foreignData;
	}
}