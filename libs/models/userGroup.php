<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class UserGroup extends SimpleModel {
	
	protected $table = "user_groups";
	protected $tablePrimaryKeys = ['id'];
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
		'group_id' => [
			'type' => 'int',
			'length' => 11
		]
	];
	
	public function __construct() {	}
	
	
}