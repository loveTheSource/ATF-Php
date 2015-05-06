<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class User extends SimpleModel {
	
	protected $table = "users";
	protected $primaryKeyColumns = array('id');
	protected $updateColumns = array('login', 'active', 'name');
	protected $columnMappings = array(
		'last_login' => 'lastLogin',
		'user_since' => 'userSince'
	);
	
	// table columns
	public $id = null;
	public $login = null;
	public $lastLogin = null;
	public $userSince = null;
	public $active = null;
	public $name = null;
	
	public function __construct() {	}
	
	/**
	 * additional user stuff
	 */	
	
	protected $groups = array();
	
	public function updateAll() {
		foreach ($this->groups AS $group) {
			$group->updateAll();
		}
		
		// save model data
		parent::updateAll();
	}

	public function setUserGroups($groups) {
		$this->groups = $groups;
	}
	
	public function getUserGroups() {
		return $this->groups;
	}
}