<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Exceptions;

use ATFApp\Core;

class DbSelector {
	
	private $dbConnection = ProjectConstants::DB_DEFAULT_CONNECTION;
	private $columns = [];
	private $table = null;
	private $wheres = [];
	private $orders = [];
	private $start = null;
	private $limit = null;
	
	private $operators = ['<', '<=', '=', '>=', '>'];
	
	/**
	 * constructor
	 * 
	 * @param string $db database connection id
	 */
	public function __construct($dbConnection=null) {
		if (!is_null($dbConnection)) {
			$this->dbConnection = $dbConnection;
		}
	}
	
	/**
	 * select (columns)
	 * 
	 * @param array|string $columns table columns or '*'
	 * @return \ATFApp\Core\DbSelector
	 */
	public function select($columns) {
		if (is_array($columns)) {
			foreach ($columns as $c) {
				if (!in_array($c, $this->columns)) {
					$this->columns[] = $c;
				}
			}
		} elseif (!in_array($columns, $this->columns)) {
			$this->columns[] = $columns;
		}
		
		return $this;
	}
	
	/**
	 * from (table)
	 * 
	 * @param from $table table name
	 * @return \ATFApp\Core\DbSelector
	 */
	public function from($table) {
		$this->table = $table;
		
		return $this;
	}
	
	/**
	 * where (clause)
	 * 
	 * @param string $column column name
	 * @param multitype $value value
	 * @param type $operator relational operator
	 * @return \ATFApp\Core\DbSelector
	 */
	public function where($column, $value, $operator='=') {
		if (in_array($operator, $this->operators)) {
			$this->wheres[] = [
				'column' => $column,
				'value' => $value,
				'operator' => $operator
			];
		}
		
		return $this;
	}
	
	/**
	 * oder by
	 * 
	 * @param string $column column name
	 * @param string $sort ASC | DESC
	 * @return \ATFApp\Core\DbSelector
	 */
	public function orderBy($column, $sort="ASC") {
		if (strtolower($sort) === "desc") {
			$this->orders[] = [
				'column' => $column,
				'sort' => 'DESC'
			];
		} else {
			$this->orders[] = [
				'column' => $column,
				'sort' => 'ASC'
			];
		}
		
		return $this;
	}
	
	/**
	 * results start + limit
	 * 
	 * @param integer $start
	 * @param integer $maxResults
	 * @return \ATFApp\Core\DbSelector
	 */
	public function limit($start, $limit) {
		$this->start = $start;
		$this->limit = $limit;
		
		return $this;
	}

	/**
	 * fetch query results
	 * 
	 * @param string $fetchType 'array' | 'class'
	 * @param string $class classname (required if fetchType is set to 'class')
	 * @return boolean|array
	 */
	public function fetchResults($fetchType='array', $class=null) {
		$statementData = $this->createQuery();
		$query = $statementData['query'];
		$params = $statementData['params'];

		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);

		$res = $statementHandler->execute($params);
		if ($res !== false && $res >= 1) {
			switch ($fetchType) {
				case "class":
					$result = $statementHandler->fetchAll(\PDO::FETCH_CLASS, $class);
					break;

				case "array":
				default:
					$result = $statementHandler->fetchAll();
				}
			
			return $result;
		}

		return false;
	}
	
	/**
	 * create query and params array for prepared statement
	 * 
	 * @return array ['query' => '...', 'params' => ['...', ] ]
	 */
	private function createQuery() {
		if (empty($this->columns)) {
			return false;
		} elseif (is_null($this->table)) {
			return false;
		}
		
		$query = "SELECT " . implode(",", $this->columns) . " FROM " . $this->table;
		$params = [];
		
		foreach ($this->wheres as $i => $w) {
			if ($i > 0) {
				$query .= " AND ";
			} else {
				$query .= " WHERE ";
			}
			$query .= $w['column'] . ' ' . $w['operator'] . ' :' . $w['column'];
			$params[$w['column']] = $w['value'];
		}
		
		foreach ($this->orders as $i => $o) {
			if ($i > 0) {
				$query .= ", ";
			} else {
				$query .= " ORDER BY ";
			}
			$query .= $o['column'] . ' ' . $o['sort'];
		}
		
		if (!is_null($this->start) && !is_null($this->limit)) {
			if ($this->getDbConnectionType() === ProjectConstants::DB_CONNECTION_TYPE_PGSQL) {
				$query .= " LIMIT " . $this->limit . " OFFSET " . $this->start;
			} else {
				$query .= " LIMIT " . $this->start . ', ' . $this->limit;
			}
		}

		return [
			'query' => $query,
			'params' => $params
		];
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

