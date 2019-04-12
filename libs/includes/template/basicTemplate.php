<?php

namespace ATFApp\Template;

use ATFApp\Core AS Core;
use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;

/**
 * templates abstract class
 * 
 */
abstract class BasicTemplate {

	protected $config = null;
	protected $templateSkin = null;
	private $templateExtension = null;
	private $templateData = [];
	
	public function __construct($templateExtension) {
		$this->templateExtension = $templateExtension;
		$this->templateSkin = BasicFunctions::getSkin();
		$this->config = BasicFunctions::getConfig('project_config');
	}
	
	/**
	 * returns the template path
	 * 
	 * @return string
	 */
	public function getTemplatePath() {
		return TEMPLATES_PATH . $this->getSkinFolder() . DIRECTORY_SEPARATOR;
	}

	/**
	 * returns skin folder (if available)
	 * 
	 * @return string
	 * @throws CustomException
	 */
	protected function getSkinFolder() {
		if (isset($this->config['skins'][$this->templateSkin]['folder'])) {
			return $this->config['skins'][$this->templateSkin]['folder'];
		} else {
			throw new Exceptions\Custom("no template folder in config: " . $this->templateSkin);	
		}
	}

	public function getModulesPath() {
		return $this->getTemplatePath() . 'modules' . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * check id template file exists
	 *
	 * @param string $templateFile
	 * @return boolean
	 */
	public function templateFileExists($templateFile) {
		return is_file($templateFile) && is_readable($templateFile);
	}
	
	# ++++++++++++++++++++ template data (get, set, ..) ++++++++++++++++++++
	
	/**
	 * assign a key/value pair to the template
	 * 
	 * @param string $key
	 * @param multitype $value
	 */
	public function setData($key, $value) {
		$this->templateData[$key] = $value;
	}
	
	/**
	 * delete a key/value pair from template
	 * 
	 * @param string $key
	 */
	
	public function deleteData($key) {
		if (isset($this->templateData[$key])) {
			unset($this->templateData[$key]);
		}
	}
	/**
	 * assign multiple key/value pairs to template
	 * 
	 * @param array $keyValueArray
	 */
	
	public function setDataArray($keyValueArray) {
		foreach ($keyValueArray AS $key => $value) {
			$this->setData($key, $value);
		}
	}
	
	/**
	 * deletes the current data assigned to the template
	 */
	public function delTemplateData() {
		$this->templateData = [];
	}
	
	
    /**
     * returns assigned template Data
     * 
     * @return array:
     */
    protected function getTemplateData() {
        return $this->templateData;
    }

    
    # ++++++++++++++++++++ template paths (action,helper) ++++++++++++++++++++

    /**
     * return path to action template file
     * 
     * @return string
     */
    protected function getActionTemplate() {
			$router = Core\Includer::getRouter();
			$routeConf = $router->getRouteConfig(BasicFunctions::getRoute());
			$templateFile = $routeConf['template'];
			$templateFolder = $routeConf['module'];
				
			return TEMPLATES_PATH . BasicFunctions::getSkin()
				. DIRECTORY_SEPARATOR . 'controller'
				. DIRECTORY_SEPARATOR . $templateFolder
				. DIRECTORY_SEPARATOR . $templateFile;
    }

    /**
     * return path to module template file
     * 
     * @return string
     */
    protected function getModuleTemplate() {
			$template = Core\Factory::getTemplateObj();
			$router = Core\Includer::getRouter();
			$routeConf = $router->getRouteConfig(BasicFunctions::getRoute());
			$templateFile = $routeConf['module'] . 'Module' . $template->templateExtension;
			$templateFolder = $routeConf['module'];
				
			return TEMPLATES_PATH . BasicFunctions::getSkin()
				. DIRECTORY_SEPARATOR . 'controller'
				. DIRECTORY_SEPARATOR . $templateFolder
				. DIRECTORY_SEPARATOR . $templateFile;
    }

    /**
     * return path to action template file
     *
     * @return string
     */
    protected function getHelperTemplate($helper) {
    	return $this->getTemplatePath() . 'helper' . DIRECTORY_SEPARATOR . $helper. $this->templateExtension;
    }
    
}

?>