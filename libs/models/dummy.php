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
	/**
	 * connection id (optional)
	 */
	protected $dbConnection = ProjectConstants::DB_DEFAULT_CONNECTION;  // default - can be overwritten here

	/**
	 * table name in db
	 */
	protected $table = "table_name";

	/**
	 * list of columns that build a primary id or at least a unique id
	 */
	protected $tablePrimaryKeys = ['id'];
	
	/**
	 * table columns
	 */
	protected $tableColumns = [
		'id' => [
			'type' => 'int',
			'length' => 11
		],
		'email' => [
			'type' => 'string',
			'length' => 32
		],
		'password' => [
			'type' => 'string',
			'length' => 64
		],
		'profile' => [
			'type' => 'text'
		],
		'is_admin' => [
			'type' => 'int',
			'length' => 1
		],
		'last_login' => [
			'type' => 'timestamp'
		]
	];
	
	/** 
	 * foreign keys (optional)
	 * 
	 * usage: $model->getForeignData('FK_column')
	 */
	protected $tableForeignKeys = [
		'FK_column' => [
			'relation' => '1-1', // 1-1, 1-m, m-m
			'model' => 'Modelclass',
			'remoteCol' => 'column'
		]
	];

	/**
	 * related tables (optional)
	 * 
	 * usage: $model->getForeignDataByRelation('new_key')
	 */
	protected $tableRelations = [
		'new_key' => [
			'sourceCol' => 'id',		// column in this model
			'model' => 'Modelclass',	// model class
			'remoteCol' => 'user_id'	// column in remote table (Modelclass)
		]
	];

	/**
	 * protecteed columns (optional)
	 * columns that may not be changed via model
	 */
	protected $tableColumnsProtected = ['user_since'];


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