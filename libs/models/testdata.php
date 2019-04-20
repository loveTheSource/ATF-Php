<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class Testdata extends SimpleModel {
	
	protected $table = "testdata";
	protected $tablePrimaryKeys = ['id'];
	// table columns
	protected $tableColumns = [
		'id',
		'name', 
		'random',
		'data',
		'user_id'
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