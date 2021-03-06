<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * includes the some widely used classes and 
 * returns an instance (singleton where required)
 * 
 * @author cre8.info
 *
 */
abstract class Includer {

	/**
	 * get router
	 * 
	 * @return \ATFApp\Core\Router
	 */
	public static function getRouter() {
		$obj = Router::getInstance();
		return $obj;
	}

	/**
	 * get lang object
	 * 
	 * @return \ATFApp\Core\Lang
	 */
	public static function getLangObj() {
		$obj = Lang::getInstance();
		return $obj;
	}
	
	/**
	 * get config object
	 * 
	 * @return \ATFApp\Core\Config
	 */
	public static function getConfigObj() {
		$obj = Config::getInstance();
		return $obj;
	}
	
	/**
	 * get validator object
	 * 
	 * @return \ATFApp\Helper\Validator
	 */
	public static function getValidatorObj() {
		$obj = new Helper\Validator();
		return $obj;
	}

	/**
	 * get auth object
	 * 
	 * @return \ATFApp\Core\Auth
	 */
	public static function getAuthObj() {
		$obj = Auth::getInstance();
		return $obj;
	}
	
	/**
	 * get document object
	 * 
	 * @return \ATFApp\Core\Document
	 */
	public static function getDocumentObj() {
		$obj = Document::getInstance();
		return $obj;
	}
	
	/**
	 * get respopnse object
	 * 
	 * @return \ATFApp\Core\Response
	 */
	public static function getResponseObj() {
		$obj = Response::getInstance();
		return $obj;
	}

	/**
	 * get statement handler (db)
	 * 
	 * @return \ATFApp\Core\Db\StatementHandler
	 */
	public static function getStatementHandler($query, $dbConnection) {
		$obj = new Db\StatementHandler($query, $dbConnection);
		return $obj;
	}

	/**
	 * get a template object
	 * 
	 * @param string $engine
	 * @param string $skin
	 * @throws CoreException
	 * @return \ATFApp\Template\TemplatePhp
	 */
	public static function getTemplateObj($engine=null, $skin=null) {
		if (is_null($engine)) {
			$engine = ProjectConstants::SYSTEM_TEMPLATE_ENGINE;
		}
		
		switch ($engine) {
			case "php":
				return self::getTemplateEnginePhp($skin);
				break;
				
			case "smarty":
				return self::getTemplateEngineSmarty($skin);
				break;
				
			default:
				throw new Exceptions\Core("unknown template: " . $engine);
		}
	}
	/**
	 * get php template object
	 * 
	 * @param string $skin
	 * @return \ATFApp\Template\TemplatePhp
	 */
	private static function getTemplateEnginePhp($skin=null) {
		require_once INCLUDES_PATH . "template" . DIRECTORY_SEPARATOR . "templatePhp.php";
		$obj = new \ATFApp\Template\TemplatePhp();
		if (!is_null($skin)) {
			$obj->setSkin($skin);
		}
		return $obj;
	}
	/**
	 * get samrty template object
	 *
	 * @param string $skin
	 * @return \ATFApp\Template\TemplateSmarty
	 */
	private static function getTemplateEngineSmarty($skin=null) {
		require_once INCLUDES_PATH . "template" . DIRECTORY_SEPARATOR . "templateSmarty.php";
		$obj = new \ATFApp\Template\TemplateSmarty();
		if (!is_null($skin)) {
			$obj->setSkin($skin);
		}
		return $obj;
	}
	
	/**
	 * get pdo db object
	 * 
	 * @param string $connectionId
	 * @return \ATFApp\Core\Db\PdoDb
	 */
	public static function getDbObj($connectionId=null) {
		if (is_null($connectionId)) $connectionId = ProjectConstants::DB_DEFAULT_CONNECTION;
		
		$obj = Db::getConnection($connectionId);
		return $obj;
	}
	
	/**
	 * return all db connections
	 * 
	 * @return array of PdoDb
	 */
	public static function getAllDbConnections() {
		return Db::getAllConnections();
	}
	
	/*
    public static function getMemcacheObj() {
    	$obj = basic_memcache::getInstance();
    	return $obj;
    } 
	*/
	
	public static function getDbSelector($dbConnection=null) {
		$obj = new Core\DbSelector($dbConnection);
		return $obj;
	}

	public static function getDbUpdater($dbConnection=null) {
		$obj = new Core\DbSelector($dbConnection);
		return $obj;
	}

}

?>