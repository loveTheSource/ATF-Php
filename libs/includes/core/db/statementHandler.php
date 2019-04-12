<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Core;

/**
 * class to handle profiling when using pdo prepared statements (execute method)
 */
class StatementHandler {
	
	private $dbConnection = 'default';
	private $useProfiler = false;
	private $statement = null;
	
	public function __construct(\PDOStatement $statement, $dbConnection=null) {
		if (BasicFunctions::useProfiler()) {
			$this->useProfiler = true;
		}

		if (!is_null($dbConnection)) {
			$this->dbConnection = $dbConnection;
		}
		
		$this->statement = $statement;
	}

	/**
	 * 
	 * $return can have the following values:
	 * cols: columns count
	 * rows: affected rows
	 * 
	 * @param array $params
	 * @param string $return select return value (cols / rows)
	 */
	public function execute($params=[], $return='rows') {
		$profilerInUse = false;
		if ($this->useProfiler) {
			$profilerInUse = true;
			$before = microtime(true);
		}
		
		$result = $this->statement->execute($params);

		if ($profilerInUse) {
			$executionTime = bcsub(microtime(true), $before, 6);
			$db = Core\Factory::getDbObj($this->dbConnection);
			if (method_exists($db, 'addProfile')) {
				$db->addProfile('EXECUTE: ' . $this->statement->queryString . "\n" . preg_replace('/[\n]/', '', var_export($params, true)), $executionTime);
			}
		}
		
		if ($result !== false) {
			if ($return === "cols") {
				return $this->statement->columnCount();
			} else {
				return $this->statement->rowCount();
			}
		}
		return false;
	}

	public function fetchAll($fetchStyle, $class) {
		return $this->statement->fetchAll($fetchStyle, $class);
	}
}

