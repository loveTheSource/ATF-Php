<?php

namespace ATFApp\TemplFuncs;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * object that offers several methods inside templates
 * 
 * @author cre8.info
 */
class TemplateFunctions {

	public $document = null;
	public $format = null;
	
	private $langObj = null;

	public function __construct() {
		$this->document = \ATFApp\Core\Document::getInstance();
		
		$this->format = new Helper\Format();
	}

	/**
	 * get translation text 
	 * 
	 * @param string $file
	 * @param string $key
	 * @param string|array $format
	 * @return string
	 */
	public function getLangText($file, $key, $format=false) {
		if (is_null($this->langObj)) {
			$this->langObj = Core\Factory::getLangObj();
		}
		return $this->langObj->getLangText($file, $key, $format);
	}
	
	/**
	 * return get param
	 * 
	 * @param string $key
	 * @return integer|string
	 */
	public function getParamGet($key) {
		return Core\Request::getParamGet($key);
	}

	/**
	 * get current language
	 * 
	 * @return string
	 */
	public function getLanguage() {
		return BasicFunctions::getLanguage();
	}

	/**
	 * get project name
	 * 
	 * @return string
	 */
	public function getProjectName() {
		return ProjectConstants::PROJECT_NAME;
	}

	/**
	 * return current route
	 * 
	 * @return string
	 */
	public function getRoute() {
		return BasicFunctions::getRoute();
	}
	
	/**
	 * return app env
	 * 
	 * @return string
	 */
	public function getEnv() {
		return BasicFunctions::getEnv();
	}
	
	/**
	 * return is production env
	 * 
	 * @return boolean
	 */
	public function isProduction() {
		return BasicFunctions::isProduction();
	}
	
	/**
	 * get the current link
	 * 
	 * @param string $absolute
	 * @return string
	 */
	public function getCurrentLink($absolute=true) {
		return $this->getLink(BasicFunctions::getRoute(), $absolute);
	}
	
	/**
	 * get an application link
	 * 
	 * @param string $route
	 * @param string $absolute
	 * @return string
	 */
	public function getLink($route, $absolute=true) {
		return BasicFunctions::getLink($route, $absolute);
	}

	/**
	 * 
	 */
	public function getWeblink() {
		return Core\Request::getHost() . WEBFOLDER;
	}
	
	/**
	 * include a template helper
	 * 
	 * @param string $helper
	 * @param array $data
	 */
	public function getHelper($helper, $data=[]) {
		try {
			$helperFile = INCLUDES_PATH . 'template' . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . $helper . '.php';
			$helperClass = '\ATFApp\Template\Helper\TemplateHelper' . ucfirst($helper);
			require_once $helperFile;
			$helperObj = new $helperClass();
			return $helperObj->getHelper($data);
		} catch (\Exception $e) {
			if (!BasicFunctions::isProduction()) {
				throw $e;
			}
			return "";
		}
	}
	
	/**
	 * include another template 
	 * 
	 * @param string $template
	 * @param array $data
	 */
	public function includeTemplate($template, $data=null) {
		$templateObj = Core\Factory::getTemplateObj();
		
		$templateFile = $templateObj->getTemplatePath() . $template;
		
		if ($templateObj->templateFileExists($templateFile)) {
			if (is_array($data)) {
				$templateObj->setDataArray($data);
			}
				
			$templateObj->renderFile($templateFile, true);
		}
	}
}

?>