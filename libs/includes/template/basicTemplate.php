<?php

namespace ATFApp\Template;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;

/**
 * templates abstract class
 * 
 */
abstract class BasicTemplate {

	protected $config = null;
	protected $modulesConfig = null;
	protected $templateSkin = null;
	private $templateExtension = null;
	private $templateData = array();
	
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
	
	protected function getModulesConfig() {
		if (is_null($this->modulesConfig)) {
			$this->modulesConfig = BasicFunctions::getConfig('modules');
		}
		return $this->modulesConfig;
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
		$this->templateData = array();
	}
	
	
    /**
     * returns assigned template Data
     * 
     * @return array:
     */
    protected function getTemplateData() {
        return $this->templateData;
    }

    
    # ++++++++++++++++++++ template paths (module,cmd,action,helper) ++++++++++++++++++++
    
    /**
     * return path to module template file
     * 
     * @return string
     */
    protected function getModuleTemplate() {
    	$module = BasicFunctions::getModule();
    	$config = $this->getModulesConfig();
    	
    	if (isset($config[$module]['template'])) {
    		return $this->getModulesPath() . $config[$module]['template'];
    	} else {
    		// user default template
    		return $this->getModulesPath() . ProjectConstants::MODULES_DEFAULT_TEMPLATE;
    	}
    }

    /**
     * return path to cmd template file 
     * 
     * @return string
     */
    protected function getCmdTemplate() {
       	$module = BasicFunctions::getModule();
       	$cmd = BasicFunctions::getCmd();
    	$config = $this->getModulesConfig();
    	
    	if (isset($config[$module]['cmds'][$cmd]['template'])) {
    		return $this->getModulesPath() . $config[$module]['cmds'][$cmd]['template'];
    	} else {
    		// user default template
    		return $this->getModulesPath() . ProjectConstants::CMDS_DEFAULT_TEMPLATE;
    	}
    }

    /**
     * return path to action template file
     * 
     * @return string
     */
    protected function getActionTemplate() {
       	$module = BasicFunctions::getModule();
       	$cmd = BasicFunctions::getCmd();
    	$action = BasicFunctions::getAction();
    	$config = $this->getModulesConfig();
    	
    	return $this->getTemplatePath() . 
    			'modules' . DIRECTORY_SEPARATOR . 
    			BasicFunctions::getModule() . DIRECTORY_SEPARATOR .
    			'cmds' . DIRECTORY_SEPARATOR .
    			BasicFunctions::getCmd() . DIRECTORY_SEPARATOR .
    			'actions' . DIRECTORY_SEPARATOR .
    			BasicFunctions::getAction(). $this->templateExtension;
    	#return $this->getActionPath() . BasicFunctions::getAction(). $this->templateExtension;
    }

    /**
     * return path to action template file
     *
     * @return string
     */
    protected function getHelperTemplate($helper) {
    	return $this->getTemplatePath() . 'helper' . DIRECTORY_SEPARATOR . $helper. $this->templateExtension;
    }
    
    /*
    protected function getModulesPath() {
    	return $this->getTemplatePath() . 'modules' . DIRECTORY_SEPARATOR . BasicFunctions::getModule() . DIRECTORY_SEPARATOR;
    }
    protected function getCmdPath() {
    	return $this->getModulesPath() . 'cmds' . DIRECTORY_SEPARATOR . BasicFunctions::getCmd() . DIRECTORY_SEPARATOR;
    }
    protected function getActionPath() {
    	return $this->getCmdPath() . 'actions' . DIRECTORY_SEPARATOR;
    }
     */
}

?>