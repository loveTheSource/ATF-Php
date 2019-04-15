<?php

namespace ATFApp\Core\Db;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Core;
use ATFApp\Models as Model;

/**
 * PdoDb extends the base php \PDO class
 * keep that in mind when using the consstructor
 */
class PdoDb extends \PDO {
	private $statementsToLog = ['insert ', 'update ', 'delete ', 'drop ta', 'truncat'];
	
	/**
	 * prepare query for prepared statement
	 * 
	 * @param string $statement
	 * @param array $options
	 * @return \PDOStatement|false
	 */
	public function prepare($statement, $options=[]) {
		$this->logQuery($statement, 'prepare');
		return parent::prepare($statement, $options);
	}
	
	/**
	 * execute query (e.g. select)
	 * that return a result set
	 * 
	 * @param string $statement
	 * @return \PDOStatement|false
	 */
	public function query($statement) {
		$this->logQuery($statement, 'query');
		return parent::query($statement);
	}
	
	/**
	 * execute query (e.g. insert, update, delete)
	 * that do not return result sets
	 * 
	 * @param string $statement
	 * @return int|false
	 */
	public function exec($statement) {
		$this->logQuery($statement, 'exec');
		return parent::exec($statement);
	}
	

	/**
	 * log query / params to db
	 * 
	 * @param string $query
	 * @param string $method
	 * @param array $params
	 */
	public function logQuery($query, $method=null, $params=[]) {
		if (ProjectConstants::DB_LOGGING === true) {
			$part = strtolower(substr(trim($query), 0, 7));
			if (in_array($part, $this->statementsToLog)) {
				// TODO ALWAYS REMEMBER: whenever you think about renaming the sql_log table... adjust the next line as well!!!
				// otherwise the code will crash because it will 'recursively' write a log for a log for a log... ;)
				if (strpos($query, 'INSERT INTO sql_log ') === false) {
					$auth = Core\Factory::getAuthObj();

					$sqlLogModel = new Model\SqlLog();
					$sqlLogModel->user_id = $auth->getUserId();
					$sqlLogModel->remote_addr = Core\Request::getRemoteAddress();
					$sqlLogModel->query = $query;
					$sqlLogModel->method = $method;
					if (!empty($params)) {
						$sqlLogModel->params = json_encode($params);
					}

					$sqlLogModel->insert();
				}
			}
		}
	}
}