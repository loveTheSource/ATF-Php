<?php

namespace ATFApp\Models;

require_once 'simpleModel.php';

class Group extends SimpleModel {
	
	protected $table = "groups";
	protected $primaryKeyColumns = array('id');
	protected $updateColumns = array('groupname', 'active');
	
	// table columns
	public $id = null;
	public $groupname = null;
	public $active = null;
	
	public function __construct() {	}
	
	
}