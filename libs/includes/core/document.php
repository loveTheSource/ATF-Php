<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;

/**
 * Core\Document
 * 
 * manages page html regarding to
 * - title
 * - language code
 * - css files
 * - js modules
 * 
 */
class Document {
	
	private static $instance = null;
	
	private $separator = "";				// separator used in title
	private $title = "";					// page title
	private $languageCode = "";				// document language code
	private $charset = "UTF-8";				// default charset
	private $favicon = null;				// favicon
	private $cssFiles = [];					// css files to include
	private $jsModules = [];				// js files to include + initialize modules
	private $jsFiles = [];					// js files to include only (e.g. external code)
	private $filterRegexp = '/[^\w]/si';	// used to generate array keys
	private $metatags = [];					// metatags to add in the document head
	
	// private to force singleton
	private function __construct() {
		$this->separator = ProjectConstants::TITLE_SEPARATOR;
		$this->setLanguage(BasicFunctions::getLanguage());
	}
	
	/**
	 * get object instance (singleton)
	 * 
	 * $renew=true to overwrite existing object instance
	 * 
	 * @param boolean $renew
	 * @return Core\Router
	 */
	public static function getInstance($renew=false) {
		if (is_null(self::$instance) || $renew) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	# +++++++++++++++++++++++ page language ++++++++++++++++++++++++++++
	
	/**
	 * set metatag
	 * 
	 * @param string $name metatag 'name'
	 * @param string $content metatag 'content'
	 */
	public function setMetatag($name, $content) {
		$this->metatags[$name] = $content;
	}
	/**
	 * get all metatags
	 * @return array
	 */
	public function getMetatags() {
		return $this->metatags;
	}

	# +++++++++++++++++++++++ page language ++++++++++++++++++++++++++++
	
	/**
	 * get language code
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->languageCode;
	}
	/**
	 * set language code
	 *
	 * @param string $lang
	 */
	public function setLanguage($lang) {
		$this->languageCode = $lang;
	}


	# +++++++++++++++++++++++ content charset ++++++++++++++++++++++++++++
	
	/**
	 * get content charset
	 *
	 * @return string
	 */
	public function getCharset() {
		return $this->charset;
	}
	/**
	 * set content charset
	 *
	 * @param string $charset
	 */
	public function setCharset($charset) {
		$this->charset = $charset;
	}
	
	
	# +++++++++++++++++++++++ page title ++++++++++++++++++++++++++++
	
	/**
	 * get page title
	 * 
	 * @return string
	 */
	public function getTitle() {
		return Helper\Format::cleanupText($this->title, false);
	}
	/**
	 * set page title (overwriting existing)
	 * 
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	/**
	 * append string to title
	 * 
	 * @param string $part
	 * @param boolean $separate
	 */
	public function appendToTitle($part, $separate=true) {
		$title = $this->title;
		if ($separate) $title .= $this->separator;
		$title .= $part;
		$this->setTitle($title);
	}
	/**
	 * prepend string to title
	 * 
	 * @param string $part
	 * @param boolean $separate
	 */
	public function prependToTitle($part, $separate=true) {
		$title = $part;
		if ($separate) $title .= $this->separator;
		$title .= $this->title;
		$this->setTitle($title);
	}
	
	
	# +++++++++++++++++++++++ page css files ++++++++++++++++++++++++++++
	
	/**
	 * addCssFile
	 * method to collect the css files to load inside html head
	 * 
	 * @param string $file
	 */
	public function addCssFile($file, $media='all') {
		if (!empty($file)) {
			$file = $this->getCssPath($file);
			$key = $this->getCssKey($file);
			if (!array_key_exists($key, $this->cssFiles)) {
				$this->cssFiles[$key] = [
					'file' => $file,
					'media' => $media
				];
			}			
		}
	}
	
	/**
	 * remove a css file from the list to include
	 * 
	 * @param string $file
	 */
	public function unregisterCssFile($file) {
		// TODO test it!!!
		if (!empty($file)) {
			$file = $this->getCssPath($file);
			$key = $this->getCssKey($file);
			if (array_key_exists($file, $this->cssFiles)) {
				$newFiles = [];
				foreach($this->cssFiles AS $fk => $css) {
					if ($fk != $key) {
						$newFiles[$fk] = $css;
					} else {
throw new Exceptions\Custom("TESTER: unregisterCssFile seems to work. consider removing this exception and the TODO above...");
					}
				}
				$this->cssFiles = $newFiles;
			}
		}
	}
	
	/**
	 * return the list of css files to include
	 * 
	 * @return array
	 */
	public function getCssFiles() {
		return $this->cssFiles;
	}
	
	public function hasJsModules() {
		return count($this->jsModules) > 0;
	}
	
	/**
	 * get array key for css file
	 * 
	 * @param string $file
	 * @return string
	 */
	private function getCssKey($file) {
		return preg_replace($this->filterRegexp, '-', $file);
	}
	
	/**
	 * get css file path
	 *
	 * @param string $file
	 * @return string
	 */
	private function getCssPath($file) {
		return WEBFOLDER_CSS . $file;;
	}
	
	
	# +++++++++++++++++++++++ page js files ++++++++++++++++++++++++++++
	
	/**
	 * add js file to document
	 * 
	 * @param string $jsFile
	 */
	public function addJsFile($jsFile) {
		if (!in_array($jsFile, $this->jsFiles)) {
			$this->jsFiles[] = $jsFile;
		}
	}

	/**
	 * get list of js files to include
	 *
	 * @return array
	 */
	public function getJsFiles() {
		return $this->jsFiles;
	}

	/**
	 * remove from list of js files to include
	 *
	 * @param string $jsFile
	 */
	public function unregisterJsFile($jsFile) {
		if (in_array($jsFile, $this->jsFiles)) {
			$newList = [];
			foreach ($this->jsFiles AS $js) {
				if ($js != $jsFile) $newList[] = $js;
			}
			$this->jsFiles = $newList;
		}
	}
	
	# +++++++++++++++++++++++ page js modules ++++++++++++++++++++++++++++
	
	/**
	 * register js module for client
	 * 
	 * @param string $file
	 * @param string $module
	 * @param array $data
	 */
	public function registerJsModule($file, $module, Array $data=[]) {
		if (!empty($module)) {
			$file = $this->getJsModulesPath($file);
			$key = $this->getJsModulesKey($file);
			if (!array_key_exists($key, $this->jsModules)) {
				$data = (!empty($data)) ? json_encode($data) : ''; 
				$this->jsModules[$key] = [
					'file' => $file,
					'module' => $module,
					'data' => $data
				];
			}			
		}
	}
	
	/**
	 * unregister js module for client
	 * 
	 * @param unknown $jsModule
	 */
	public function unregisterJsModule($jsModule) {
		// TODO test it!!!
		if (!empty($jsModule)) {
			$file = $this->getJsModulesPath($file);
			$key = $this->getJsKey($file);
			if (array_key_exists($key, $this->jsModules)) {
				$newModules = [];
				foreach($this->jsModules AS $mk => $js) {
					if ($mk != $key) {
						$newModules[$mk] = $js;
					} else {
throw new Exceptions\Custom("TESTER: unregisterJsModule seems to work. consider removing this exception and the TODO above...");
					}
				}
				$this->jsModules = $newModules;
			}
		}
	}
	
	/**
	 * get registered js modules
	 * 
	 * @return array
	 */
	public function getJsModules() {
		return $this->jsModules;
	}
	
	/**
	 * get array key for js file
	 *
	 * @param string $file
	 * @return string
	 */
	private function getJsModulesKey($file) {
		return preg_replace($this->filterRegexp, '-', $file);
	}
	
	/**
	 * get js file path
	 *
	 * @param string $file
	 * @return string
	 */
	private function getJsModulesPath($file) {
		return WEBFOLDER_JS . 'modules/' . $file;
	}
	
	
	
	# +++++++++++++++++++++++ page favicon ++++++++++++++++++++++++++++
	
	/**
	 * get favicon
	 *
	 * @return string
	 */
	public function getFavicon() {
		return $this->favicon;
	}
	/**
	 * set favicon
	 *
	 * @param string $favicon
	 */
	public function setFavicon($favicon) {
		$this->favicon = $favicon;
	}
	
	
}