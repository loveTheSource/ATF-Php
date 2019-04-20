<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class UserGroup extends SimpleModel {
	
	protected $table = "user_groups";
	protected $tablePrimaryKeys = ['id'];
	// table columns
	protected $tableColumns = [
		'id',
		'user_id',
		'group_id'
	];
	
	public function __construct() {	}
	
	
}