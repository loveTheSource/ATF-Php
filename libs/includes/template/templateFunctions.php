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
	 * return current module
	 * 
	 * @return string
	 */
	public function getModule() {
		return BasicFunctions::getModule();
	}
	/**
	 * return current cmd
	 * 
	 * @return string
	 */
	public function getCmd() {
		return BasicFunctions::getCmd();
	}
	/**
	 * return current action
	 * 
	 * @return string
	 */
	public function getAction() {
		return BasicFunctions::getAction();
	}
	
	/**
	 * get the current link
	 * 
	 * @param string $moduleOnly
	 * @param string $moduleCmdOnly
	 * @param string $absolute
	 * @return string
	 */
	public function getCurrentLink($moduleOnly=false, $moduleCmdOnly=false, $absolute=true) {
		if ($moduleOnly) {
			// link to the current module only
			return $this->getLink(BasicFunctions::getModule(), null, null, $absolute);
		} elseif ($moduleCmdOnly) {
			// link to the current module/cmd
			return $this->getLink(BasicFunctions::getModule(), BasicFunctions::getCmd(), null, $absolute);
		} else {
			// complete current link (module/cmd/action)
			return $this->getLink(BasicFunctions::getModule(), BasicFunctions::getCmd(), BasicFunctions::getAction(), $absolute);
		}
	}
	
	/**
	 * get an application link
	 * 
	 * @param string $module
	 * @param string $cmd
	 * @param string $action
	 * @param string $absolute
	 * @return string
	 */
	public function getLink($module, $cmd=null, $action=null, $absolute=true) {
		return BasicFunctions::getLink($module, $cmd, $action, $absolute);
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
	public function getHelper($helper, $data=array()) {
		try {
			$helperFile = INCLUDES_PATH . 'template' . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . $helper . '.php';
			$helperClass = '\ATFApp\Template\Helper\TemplateHelper' . ucfirst($helper);
			require_once $helperFile;
			$helperObj = new $helperClass();
			return $helperObj->getHelper($data);
		} catch (\Exception $e) {
			if (!BasicFunctions::isLive()) {
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