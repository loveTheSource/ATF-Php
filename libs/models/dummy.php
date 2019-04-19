<?php
/**
 * dummy model 
 * 
 * cannot be used (constructor private)
 */
namespace ATFApp\Models;

use ATFApp\ProjectConstants;

require_once 'simpleModel.php';

class Dummy extends SimpleModel {
	// connection id
	// protected $dbConnection = ProjectConstants::DB_DEFAULT_CONNECTION;  // default - can be overwritten here

	// table name in db
	protected $table = "table_name";

	// list of columns that build a primary id or at least a unique id
	protected $tablePrimaryKeys = ['id'];
	
	// foreign keys
	protected $tableForeignKeys = [
		'FK_column' => [
			'relation' => '1-1', // 1-1, 1-m, m-m
			'model' => 'Modelname',
			'column' => 'column'
		]
	];

	// protecteed columns... may not be changed
	protected $tableColumnsProtected = ['user_since'];

	// table columns
	protected $tableColumns = [
		'id',
		'email',
		'password',
		'last_login',
		'FK_whatever'
	];
	
	/** 
	 * private constructor
	 * its a dummy...
	 */
	private function __construct() { }
	private function __clone() { }


	/**
	 * additional stuff
	 */	
	
}