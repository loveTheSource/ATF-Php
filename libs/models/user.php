<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class User extends SimpleModel {
	
	// protected $dbConnection = 'default';
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
	
	/*
	public $id = null;
	public $login = null;
	public $password = null;
	public $lastLogin = null;
	public $userSince = null;
	public $active = null;
	public $name = null;
	*/

	public function __construct() {	}
	
	/**
	 * additional user stuff
	 */	
	
	protected $userInGroups = [];
	
	public function updateAll() {
		// save model data
		parent::updateAll();
	}

	public function setUserGroups($groups) {
		$this->userInGroups = $groups;
	}
	
	public function getUserGroups() {
		return $this->userInGroups;
	}
}