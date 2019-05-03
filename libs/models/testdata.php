<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class Testdata extends SimpleModel {
	
	protected $table = "testdata";
	protected $tablePrimaryKeys = ['id'];
	// table columns
	protected $tableColumns = [
		'id' => [
			'type' => 'int',
			'length' => 11
		],
		'name' => [
			'type' => 'string',
			'length' => 32
		], 
		'random' => [
			'type' => 'int',
			'length' => 11
		],
		'data' => [
			'type' => 'int',
			'length' => 128
		],
		'user_id' => [
			'type' => 'int',
			'length' => 11
		]
	];
	protected $tableForeignKeys = [
		'user_id' => [
			'relation' => '1-m', // 1-1, 1-m, m-m
			'model' => 'User',
			'remoteCol' => 'id'
		]
	];

	public function __construct() {	}
	
	
}