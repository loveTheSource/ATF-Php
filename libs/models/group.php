<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class Group extends SimpleModel {
	
	protected $table = "groups";
	protected $tablePrimaryKeys = ['id'];
	// table columns
	protected $tableColumns = [
		'id' => [
			'type' => 'int',
			'length' => 11
		],
		'groupname' => [
			'type' => 'string',
			'length' => 32
		], 
		'active' => [
			'type' => 'int',
			'length' => 1
		]
	];
	
	public function __construct() {	}
	
	
}