<?php

namespace ATFApp\Models;

use ATFApp\ProjectConstants;

require_once 'simpleModel.php';

class User extends SimpleModel {
	// protected $dbConnection = ProjectConstants::DB_DEFAULT_CONNECTION;
	protected $table = "users";
	protected $tablePrimaryKeys = ['id'];
	// table columns
	protected $tableColumns = [
		'id',
		'login',
		'password',
		'last_login',
		'user_since',
		'active',
		'name'
	];
	protected $tableColumnsProtected = ['user_since'];
	protected $tableRelations = [
		'userdata' => [
			'sourceCol' => 'id',
			'model' => 'Testdata',
			'remoteCol' => 'user_id'
		]
	];

	public function __construct() {	}
	
	/**
	 * additional user stuff
	 */	
	
	protected $userInGroups = [];
	
	public function setUserGroups($groups) {
		$this->userInGroups = $groups;
	}
	
	public function getUserGroups() {
		return $this->userInGroups;
	}
}