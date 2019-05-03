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
		'id' => [
			'type' => 'int',
			'length' => 11
		],
		'login' => [
			'type' => 'string',
			'length' => 32
		],
		'password' => [
			'type' => 'string',
			'length' => 64
		],
		'name' => [
			'type' => 'string',
			'length' => 32
		],
		'active' => [
			'type' => 'int',
			'length' => 1
		],
		'last_login' => [
			'type' => 'timestamp'
		],
		'user_since' => [
			'type' => 'timestamp'
		]
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