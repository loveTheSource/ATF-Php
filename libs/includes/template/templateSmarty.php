<?php






# ===================== totally untested... just wrote the smarty version down ===================





namespace ATFApp\Template;

use ATFApp\TemplFuncs as TemplFuncs;

/**
 * smarty template engine connector
 */
require_once 'basicTemplate.php';

class TemplateSmarty extends BasicTemplate {
	
	// TODO adjust path to smarty
	private $smartyClass = "Smarty.class.php";
	public $templateExtension = ".tpl";
	
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
		if ($display) {
			echo $this->renderSmarty($templateFile, $display);
		} else {
			return $this->renderSmarty($templateFile, $display);
		}
	}
	
	/**
	 * render module template
	 * 
	 * @param boolean $display
	 * @return string
	 */
	public function renderModule($display=false) {
		$path = $this->getModuleTemplate();
		return $this->renderFile($path, $display);
	}
	
	/**
	 * render cmd template
	 * 
	 * @param boolean $display
	 * @return string
	 */
	public function renderCmd($display=false) {
		$path = $this->getCmdTemplate();
		return $this->renderFile($path, $display);
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
	private function renderSmarty($templateFile, $display=false) {
		try {
			if (!$this->templateFileExists($templateFile)) {
				throw new Exceptions\Custom("smarty template file not found: " . $templateFile);
			}
			
			// create object
			require_once $this->smartyClass;
			$smartyObj = new Smarty;
			
			// configure smarty
			$use_cache = false;  //  TODO - for using cache.. cache ids have to be defined... ???? test it!!
			$smartyObj->caching = $use_cache;
			$smartyObj->debugging = !BasicFunctions::isLive();
			
			// assign data
			$data = $this->getTemplateData();
			$smartyObj->assign('data', $data);
			
			// assign template functions obj
			require_once INCLUDES_PATH . "template" . DIRECTORY_SEPARATOR . "templateFunctions.php";
			$obj = new TemplFuncs\TemplateFunctions();
			$smartyObj->assignByRef('obj', $obj);
			
			// render
			if ($display) {
				$smartyObj->display($templateFile);
			} else {
				return $smartyObj->fetch($templateFile);
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

}

?>