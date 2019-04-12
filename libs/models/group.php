<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class Group extends SimpleModel {
	
	protected $table = "groups";
	protected $tablePrimaryKeys = ['id'];
	// table columns
	protected $tableColumns = [
		'id',
		'groupname', 
		'active'
	];
	
	public function __construct() {	}
	
	
}