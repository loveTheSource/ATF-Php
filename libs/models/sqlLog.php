<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class SqlLog extends SimpleModel {
	
	protected $table = "sql_log";
	protected $tablePrimaryKeys = array('id');
	// table columns
	protected $tableColumns = [
		'id' => [
			'type' => 'int',
			'length' => 11
		],
		'user_id' => [
			'type' => 'int',
			'length' => 11
		],
		'remote_addr' => [
			'type' => 'string',
			'length' => 64
		],
		'query' => [
			'type' => 'text'
		],
		'method' => [
			'type' => 'string',
			'length' => 16
		],
		'timestamp' => [
			'type' => 'timestamp'
		],
		'params' => [
			'type' => 'text'
		]
	];
	protected $tableColumnsProtected = ['timestamp'];
    
	public function __construct() {	}
	
}