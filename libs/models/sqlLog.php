<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class SqlLog extends SimpleModel {
	
	protected $table = "sql_log";
	protected $tablePrimaryKeys = array('id');
	// table columns
	protected $tableColumns = [
		'id',
		'user_id',
		'remote_addr',
		'query',
		'method',
		'timestamp'
	];
	protected $tableColumnsProtected = ['timestamp'];
    
	public function __construct() {	}
	
}