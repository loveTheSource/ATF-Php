<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Exceptions;

use ATFApp\Core;

class DbUpdater {
	
	private $dbConnection = ProjectConstants::DB_DEFAULT_CONNECTION;
	private $table = null;
	private $updateValues = [];
	private $wheres = [];
	
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
	 * update (table)
	 * 
	 * @param string $table 
	 * @return \ATFApp\Core\DbUpdater
	 */
	public function update($table) {
		$this->table = $table;
		
		return $this;
	}
	
	/**
	 * set (col, value)
	 * 
	 * @param string $column
	 * @param string $value
	 * @return \ATFApp\Core\DbUpdater
	 */
	public function set($column, $value) {
		$this->updateValues[$column] = $value;
		
		return $this;
	}
	
	/**
	 * set (dataArray)
	 * 
	 * @param array $dataArray
	 * @return \ATFApp\Core\DbUpdater
	 */
	public function setMulti(Array $dataArray) {
		foreach($dataArray as $col => $value) {
			$this->set($col, $value);
		}
		
		return $this;
	}
	
	/**
	 * where (clause)
	 * 
	 * @param string $column column name
	 * @param multitype $value value
	 * @param type $operator relational operator
	 * @return \ATFApp\Core\DbUpdater
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
	 * update table
	 * 
	 * @return integer 
	 */
	public function execute() {
		$statementData = $this->createQuery();
		$query = $statementData['query'];
		$params = $statementData['params'];
		
		$statementHandler = Includer::getStatementHandler($query, $this->dbConnection);
		$res = $statementHandler->execute($params);
		
		if ($res !== false) {
			return $res;
		}

		return 0;
	}
	
	/**
	 * create query and params array for prepared statement
	 * 
	 * @return array ['query' => '...', 'params' => ['...', ] ]
	 */
	private function createQuery() {
		if (empty($this->updateValues)) {
			return false;
		} elseif (is_null($this->table)) {
			return false;
		}
		
		$query = "UPDATE " . $this->table . " SET ";
		$params = [];

		foreach ($this->updateValues as $col => $value) {
			$query .= " " . $col . " = :" . $col;
			$params[$col] = $value;
	
		}
		
		foreach ($this->wheres as $i => $w) {
			if ($i > 0) {
				$query .= " AND ";
			} else {
				$query .= " WHERE ";
			}
			$query .= $w['column'] . ' ' . $w['operator'] . ' :' . $w['column'];
			$params[$w['column']] = $w['value'];
		}

		return [
			'query' => $query,
			'params' => $params
		];
	}
}

