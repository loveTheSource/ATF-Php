<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Exceptions;

use ATFApp\Core;

class DbSelector {
	
	private $db = 'default';
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
	public function __construct($db=null) {
		if (!is_null($db)) {
			$this->db = $db;
		}
	}
	
	/**
	 * select (columns)
	 * 
	 * @param array|string $columns table columns
	 * @return \ATFApp\Core\PdoSelector
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
	 * @return \ATFApp\Core\PdoSelector
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
	 * @return \ATFApp\Core\PdoSelector
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
	 * @return \ATFApp\Core\PdoSelector
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
	 * @return \ATFApp\Core\PdoSelector
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
	 * @param string $class classname
	 * @return boolean
	 */
	public function fetchResults($fetchType='array', $class=null) {
		$statementData = $this->createQuery();
		$query = $statementData['query'];
		$params = $statementData['params'];
		
		$db = Core\Factory::getDbObj($this->db);
		
		$statement = $db->prepare($query);
		
		$statementHandler = new Core\StatementHandler($statement);
		$res = $statementHandler->execute($params);
		
		if ($res === true) {
			switch ($fetchType) {
				case "class":
					$result = $statement->fetchAll(\PDO::FETCH_CLASS, $class);
					break;

				case "array":
				default:
					$result = $statement->fetchAll();
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
				$query .= " && ";
			} else {
				$query .= " WHERE ";
			}
			$query .= $w['column'] . ' ' . $w['operator'] . ' ?';
			$params[] = $w['value'];
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
			$query .= " LIMIT " . $this->start . ', ' . $this->limit;
		}
		
		return [
			'query' => $query,
			'params' => $params
		];
	}
}
