<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Models as Model;

class PdoDb extends \PDO {
	#  you cannot cache pdo statements. even cloning doesnt work...
	#  caching will is done in the model classes
	private $statementsToLog = ['insert', 'update', 'delete'];
	
	public function query($statement) {
		$this->logQuery($statement, 'query');
		return parent::query($statement);
	}
	
	public function exec($statement) {
		$this->logQuery($statement, 'exec');
		return parent::exec($statement);
	}
	
	public function logQuery($query, $method=null) {
		if (ProjectConstants::DB_LOGGING === true) {
			$part = strtolower(substr(trim($query), 0, 6));
			if (in_array($part, $this->statementsToLog)) {
				// TODO ALWAYS REMEMBER: whenever you think about renaming the sql_log table... adjust the next line as well!!!
				// otherwise the code will crash because it will 'recursively' write a log for a log for a log... ;)
				if (strpos($query, 'INSERT INTO sql_log ') === false && strpos($query, 'INSERT INTO `sql_log` ') === false) {
					$auth = Factory::getAuthObj();

					$sqlLogModel = new Model\SqlLog();
					$sqlLogModel->user_id = $auth->getUserId();
					$sqlLogModel->operator_id = BasicFunctions::getOperator();
					$sqlLogModel->remote_addr = Request::getRemoteAddress();
					$sqlLogModel->query = $query;
					$sqlLogModel->method = $method;

					$sqlLogModel->insert();					
				}
			}
		}
	}
}