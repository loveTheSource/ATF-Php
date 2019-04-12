<?php

namespace ATFApp\Models;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Exceptions;

use ATFApp\Helper;
use ATFApp\Core;

require_once 'baseModel.php';

abstract class SimpleModel extends BaseModel {
	
	protected $dbConnection = 'default';

	public function __construct() {	}
	
	/**
	 * update database entry (all possible columns)
	 * 
	 * @throws DbException
	 * @return boolean
	 */
	public function updateAll() {
		return $this->update($this->getUpdateColumns());
	}

	/**
	 * update database entry (only given columns)
	 * 
	 * @param array $columns
	 * @throws DbException
	 * @return boolean
	 */
	public function update(Array $columns) {
		$updateCols = $this->getUpdateColumns();
		$table = $this->getTable();
		
		$db = $this->getDb();
	
		$query = "UPDATE `" . $table . "` SET ";
		
		$c = 0;
		$params = [];
		foreach ($columns AS $i => $col) {
			if (!in_array($col, $updateCols)) {
				throw new Exceptions\Db("cannot update column '" . var_export($col, true) . "' in table " . $table);
			} else {
				if ($c != 0) $query .= ", ";
				$colName = $col;
				$query .= " `" . $colName . "` = :" . $colName;
				$params[$colName] = $this->$col;
				$c++;
			}
		}
	
		$query .= " WHERE " . $this->getWherePrimaries($db) . "; ";
		
		$statement = $db->prepare($query);
		$statementHandler = new Core\StatementHandler($statement, $this->dbConnection);
		$res = $statementHandler->execute($params);
		if ($res !== false) {
			return $res;
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
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
		
		$db = $this->getDb();
		
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
			$queryCols .= ' `' . $col . '`';
			$queryData[$col] = $this->$col;
			$queryVals .= ' :' . $col;
			$c++;
		}
		
		$queryCols .= ') ';
		$queryVals .= ') ';
		
		$query .= $queryCols . ' VALUES ' . $queryVals . '; ';

		$statement = $db->prepare($query);
		$statementHandler = new Core\StatementHandler($statement, $this->dbConnection);
		$res = $statementHandler->execute($queryData);
	
		if ($res !== false) {
			// try set the last insert id to the model as primary key
			$primaryKeyColumns = $this->getPrimaryKeyColumns();
			if (count($primaryKeyColumns) == 1) {
				$primaryKey = $primaryKeyColumns[0];
				$lastInsertId = $db->lastInsertId();
				$this->$primaryKey = $lastInsertId;
			}
			return true;
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
		}
	}
	
	
	/** 
	 * delete row from db
	 * 
	 * @throws Exceptions\Db
	 * @return boolean
	 */
	public function delete() {
		$db = $this->getDb();
		$queryParams = [];
		
		$query = 'DELETE FROM `' . $this->getTable() . '` WHERE ';
		foreach ($this->getPrimaryKeyColumns() as $i => $col) {
			if ($i !== 0) {
				$query .= ' && ';
			}
			$queryParams[$col] = $this->$col;
			$query .= ' `' . $col . '` = :'.$col;
		}
		$query .= ';';
		
		$statement = $db->prepare($query);
		$statementHandler = new Core\StatementHandler($statement, $this->dbConnection);
		$res = $statementHandler->execute($queryParams);

		if ($res !== false) {
			return $res;
		} else {
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
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
	
		$db = $this->getDb();
	
		$query = "SELECT * FROM `" . $this->getTable() . "` WHERE ";
	
		$keysCounter = 0;
		$params = [];
		foreach ($primaryKeys AS $k) {
			// add primary key to where clause
			if ($keysCounter != 0) {
				$query .= ' && ';
			}
			$query .= ' `' . $k . '` = :' . $k;
			$params[$k] = $keys[$k];
			$keysCounter++;
		}
		$query .= "; ";
		$statement = $db->prepare($query);
		
		$statementHandler = new Core\StatementHandler($statement, $this->dbConnection);
		$result = $statementHandler->execute($params, 'cols');
		
		if ($result !== false) {
			$modelsList = $statement->fetchAll(\PDO::FETCH_CLASS, $modelClass);
			
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
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
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
	
		$db = $this->getDb();
	
		$query = "SELECT * FROM `" . $this->getTable() . "` WHERE ";
	
		$keysCounter = 0;
		$params = [];
		foreach ($columnValues AS $col => $val) {
			if ($keysCounter !== 0) {
				$query .= ' && ';
			}
			$query .= ' `' . $col . '` = :' . $col;
			$params[$col] = $val;
			$keysCounter++;
		}
		$query .= "; ";

		$statement = $db->prepare($query);
		
		$statementHandler = new Core\StatementHandler($statement, $this->dbConnection);
		$result = $statementHandler->execute($params, 'cols');
		
		if ($result !== false) {
			$modelsList = $statement->fetchAll(\PDO::FETCH_CLASS, $modelClass);
			
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
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
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
		
		$statement = $db->prepare($query);
		
		$statementHandler = new Core\StatementHandler($statement, $this->dbConnection);
		$result = $statementHandler->execute($params);
		if ($result === true) {
			$modelsList = $statement->fetchAll(\PDO::FETCH_CLASS, $modelClass);
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
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
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
		$db = $this->getDb();
		
		// build query
		$query = "SELECT * FROM `" . $this->getTable() . "`";
		
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
					$query .= '`' . $col . '` ' . $sort;
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
				$query .= ' `' . $k . '` ASC ';
				$keysCounter++;
			}
		}
		
		if (!is_null($limit)) {
			$query .= " LIMIT " . (int)$start . ", " . (int)$limit;
		}
		
		$query .= "; ";
	
		$statement = $db->prepare($query);
		// select from db		
		$statementHandler = new Core\StatementHandler($statement, $this->dbConnection);
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
			throw new Exceptions\Db(__CLASS__ . '::' . __METHOD__ . ' failed: ' . $query, null, null, $db->errorInfo());
		}
		
	}
	
	/**
	 * create WHERE statement
	 * to select by primary keys
	 * 
	 * @param \PDO $db
	 * @return string
	 */
	private function getWherePrimaries(\ATFApp\Core\PdoDb $db) {
		$primaryKeyColumns = $this->getPrimaryKeyColumns();
		$where = "";
		
		foreach ($primaryKeyColumns AS $i => $col) {
			if ($i != 0) $where .= " && ";
			$where .= ' `' . $col . '` = ' . $db->quote($this->$col);
		}
		
		return $where;
	}
}