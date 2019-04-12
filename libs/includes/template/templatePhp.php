<?php

namespace ATFApp\Template;

use ATFApp\TemplFuncs as TemplFuncs;
use ATFApp\Exceptions;

/**
 * tiny php template engine
 */

require_once 'basicTemplate.php';

class TemplatePhp extends BasicTemplate {
	
	public $templateExtension = ".phtml";
	
	public function __construct() {
		parent::__construct($this->templateExtension);
	}

	/**
	 * render a template file given
	 * 
	 * @param string $templateFile
	 * @param boolean $display
	 * @return string
	 */
	public function renderFile($templateFile, $display=false) {
		$htmlCode = $this->includeFile($templateFile);
		
		if ($display) {
			echo $htmlCode;
		} else {
			return $htmlCode;
		}
	}
	
	/**
	 * render action template
	 * 
	 * @param boolean $display
	 * @return string
	 */
	public function renderAction($display=false) {
		$path = $this->getActionTemplate();
		return $this->renderFile($path, $display);
	}
	
	/**
	 * render module template
	 * 
	 * @param boolean $display
	 * @return string
	 */
	public function renderModule($display=false) {
		$path = $this->getModuleTemplate();
		if (file_exists($path)) {
			return $this->renderFile($path, $display);
		}
		return false;
	}
	
	/**
	 * render action template
	 * 
	 * @param boolean $display
	 * @return string
	 */
	public function renderHelper($helper, $display=false) {
		$path = $this->getHelperTemplate($helper);
		return $this->renderFile($path, $display);
	}
	
	/**
	 * include the template file
	 * the actual rendering
	 * 
	 * @param string $templateFile
	 * @throws CustomException
	 * @return string
	 */
	private function includeFile($templateFile) {
		try {
			if (!$this->templateFileExists($templateFile)) {
				throw new Exceptions\Custom("php template file not found: " . $templateFile);
			}
			
			// require class for template functions
			require_once INCLUDES_PATH . "template" . DIRECTORY_SEPARATOR . "templateFunctions.php";
			$obj = new TemplFuncs\TemplateFunctions();
			
			// get template data
			$data = $this->getTemplateData();
			
			// start buffer
			ob_start();

			// load template
			include ($templateFile);
			$htmlCode = ob_get_contents();
			
			// clean buffer
			ob_end_clean();
			
			return $htmlCode;
		} catch (\Exception $e) {
			throw $e;
		}
	}

}

?>