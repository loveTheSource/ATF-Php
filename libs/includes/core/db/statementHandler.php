<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Core;

/**
 * class to handle profiling when using pdo prepared statements (execute method)
 */
class StatementHandler {
	
	private $useProfiler = false;
	private $statement = null;
	
	public function __construct(\PDOStatement $statement) {
		if (BasicFunctions::useProfiler()) {
			$this->useProfiler = true;
		}
		
		$this->statement = $statement;
	}
	
	public function execute($params=[]) {
		$profilerInUse = false;
		if ($this->useProfiler) {
			$profilerInUse = true;
			$before = microtime(true);
		}
		
		$db = Core\Factory::getDbObj();
		$db->logQuery($this->statement->queryString . implode(', ', $params), 'exec');
		
		$result = $this->statement->execute($params);
		
		if ($profilerInUse) {
			$executionTime = bcsub(microtime(true), $before, 6);
			$db = Core\Factory::getDbObj();
			if (method_exists($db, 'addProfile')) {
				$db->addProfile('EXECUTE: ' . $this->statement->queryString . "\n" . preg_replace('/[\n]/', '', var_export($params, true)), $executionTime);
			}
		}
		
		return $result;		
	}
}

