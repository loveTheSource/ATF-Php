<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * handles a request after the bootstrap
 * 
 * @author cre8.info
 *
 */
class Handler {
	
	private $moduleObj = null;
	private $cmdObj = null;
	private $actionMethod = null;
	
	public function __construct() { 
		$this->moduleObj = Factory::getModule(BasicFunctions::getModule());
		$this->cmdObj = Factory::getCmd(BasicFunctions::getModule(), BasicFunctions::getCmd());
		$this->actionMethod = Router::getInstance()->getActionMethod(BasicFunctions::getAction());
	}
	
	/**
	 * handle the request
	 * create module/cmd objects and execute methods
	 */
	public function handle() {
		try {
			$actionHtml = $this->getActionContent();
			$cmdHtml = $this->getCmdContent($actionHtml);
			$moduleHtml = $this->getModuleContent($cmdHtml);
			
			$template = Factory::getTemplateObj();
			$template->setData('module_html', $moduleHtml);
			$template->setData('project_config', BasicFunctions::getConfig('project'));
			$templateFile = $template->getTemplatePath() . "index.phtml";
			$html = $template->renderFile($templateFile);
			
			$response = new Response();
			$response->respondHtml($html, false);
		} catch (\Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * called after the request is done
	 */
	public function postActions() {
		if (BasicFunctions::useProfiler()) {
			Helper\Profiler::stopProfiler();
			echo Helper\Profiler::getProfileHtml();
			
			$dbConnections = Core\Factory::getAllDbConnections();
			foreach ($dbConnections AS $dbId => $dbConn) {
				echo $dbConn->getProfilerHtml($dbId);
			}
		}
		
		if (!BasicFunctions::isLive()) {
			$this->printDebugInfos(false);
		}		
			
		exit(); // we're done
	}
	
	/**
	 * get module content 
	 * 
	 * @param string $cmdHtml
	 * @return string
	 */
	private function getModuleContent($cmdHtml) {
		if ($this->moduleObj->canAccess()) {
			$moduleContent = $this->moduleObj->getModuleData();
				
			// render template
			$template = Factory::getTemplateObj();
			$template->setData('module', $moduleContent);
			$template->setData('cmd_html', $cmdHtml);
			$html = $template->renderModule();
				
			return $html;
		} else {
			$this->handleAccessDenied();
		}
	}
	
	/**
	 * get cmd content
	 * 
	 * @param string $actionHtml
	 * @return string
	 */
	private function getCmdContent($actionHtml) {
		if ($this->moduleObj->canAccess() && $this->cmdObj->canAccess()) {
			$cmdContent = $this->cmdObj->getCmdData();
			
			// render template
			$template = Factory::getTemplateObj();
			$template->setData('cmd', $cmdContent);
			$template->setData('action_html', $actionHtml);
			$html = $template->renderCmd();
			
			return $html;
		} else {
			$this->handleAccessDenied();
		}
	}
	
	
	/**
	 * get action content
	 * 
	 * @return string
	 */
	private function getActionContent() {
		if ($this->moduleObj->canAccess() && $this->cmdObj->canAccess()) {
			$actionMethod = $this->actionMethod;
			
			// the action content can be either a string (html)
			// or an array in which case the corresponding template is used
			
			$actionContent = $this->cmdObj->$actionMethod();
			
			if (is_string($actionContent)) {
				return $actionContent;
			} else {
				$template = Factory::getTemplateObj();
				if (is_array($actionContent)) {
					foreach ($actionContent AS $key => $value) {
						$template->setData($key, $value);
					}
				}
				$html = $template->renderAction();
				return $html;
			}
			return "";
		} else {
			$this->handleAccessDenied();
		}
	}


	/**
	 * handle access denied
	 * 
	 * if user not logged in
	 * 	forward to login and try to redirect on auth success
	 * else
	 *  forward to 403-forbidden module
	 */
	private function handleAccessDenied() {
		$auth = Auth::getInstance();
		$forwarder = new Helper\Forward();
			
		if (!$auth->isLoggedIn()) {
			// try to login
			if (Core\Request::isGetRequest()) {
				$auth->setRedirectOnAuth(Request::getRequestURL(true, true));
			}
			// forward to login (auth module)
			$forwarder->forwardTo(ProjectConstants::MODULE_AUTH);
		} else {
			// 403 forbidden
			$forwarder->forwardTo(ProjectConstants::MODULE_403);
		}
	}
	
	
	/**
	 * print debug infos
	 */
	private function printDebugInfos($short=true) {
		echo "
		<script type=\"text/javascript\">
		function showHide(id) {
			var block = document.getElementById(id);
			if (block.style.display != 'inline') {
				block.style.display = 'inline';
			} else {
				block.style.display = 'none';
			}
		}
		</script>
		<style type=\"text/css\">
		div.debuginfos_link_show_hide {
			cursor: pointer;
		}
		div.debuginfos_link_show_hide span.debuginfos_arrows_more {
			border: 1px solid #DDDDDD;
		}
		div.debuginfos_elem {
			display: none;
		}
		</style>
		";
		// measure final execution time here
		$executionTime = bcsub(microtime(true), $_SERVER["REQUEST_TIME_FLOAT"], 6);
		echo '<pre><fieldset style="font-size: 12px;"><legend onclick="showHide(\'atf_debug_infos\');" style="cursor:pointer;"><b>D E B U G</b></legend>';
		
		echo '<table id="atf_debug_infos" style="display:inline;">';
			echo '<tr>';
				echo '<th align="left">Module:</th>';
				echo '<td>' . BasicFunctions::getModule() . '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<th align="left">Cmd:</th>';
				echo '<td>' . BasicFunctions::getCmd() . '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<th align="left">Action:</th>';
				echo '<td>' . BasicFunctions::getAction() . '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<th align="left">Memory</th>';
				echo '<td>' . Helper\Format::formatBytes(memory_get_usage()) . '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<th align="left">Memory Peak</th>';
				echo '<td>' . Helper\Format::formatBytes(memory_get_peak_usage()) . '</td>';
			echo '</tr>';

			echo '<tr>';
				echo '<th align="left">Execution Time</th>';
				echo '<td>' . $executionTime . ' sec</td>';
			echo '</tr>';

			if ($short) {
				echo '<tr>';
					echo '<th align="left">Files Included</th>';
					echo '<td>' . count(get_included_files()) . '</td>';
				echo '</tr>';
				
			} else {
				
				$incFiles = get_included_files();
				$filesCounter = count($incFiles);
				$bytesIncluded = 0;
				$filesString = "";
				foreach ($incFiles as $filename) {
					$filesize = filesize($filename);
					$bytesIncluded += $filesize;
					$filesString .= $filename . ' - ' . Helper\Format::formatBytes($filesize) . '<br/>';
				}
				unset($incFiles);
				
				echo '<tr>';
					echo '<th align="left">PHP Include Path</th>';
					echo '<td>' . get_include_path() . '</td>';
				echo '</tr>';
									
				echo '<tr>';
					echo '<th align="left" valign="top">Included Files</th>';
					echo '<td>';
						echo '<div class="debuginfos_link_show_hide" onclick="showHide(\'debuginfos_elem_php_included_files\');">';
							echo $filesCounter . ' (' . Helper\Format::formatBytes($bytesIncluded) . ')';
							echo '<span class="debuginfos_arrows_more">&#8595; &#8595; &#8595;</span>';
						echo '</div>';
						echo '<div class="debuginfos_elem" id="debuginfos_elem_php_included_files">';
							echo $filesString;
						echo '</div>';
					echo '</td>';
				echo '</tr>';
					
				echo '<tr>';
					echo '<th align="left">Session Id</th>';
					echo '<td>' . session_id() . '</td>';
				echo '</tr>';

				echo '<tr>';
					echo '<th align="left" valign="top">POST Data</th>';
					echo '<td>';
					foreach ($_POST AS $key => $value) {
						echo '<div>' . $key . ': ' . var_export($value, true) . '</div>';
					}
					 echo '</td>';
				echo '</tr>';
								
				echo '<tr>';
					echo '<th align="left" valign="top">GET Data</th>';
					echo '<td>';
					foreach ($_GET AS $key => $value) {
						echo '<div>' . $key . ': ' . var_export($value, true) . '</div>';
					}
					 echo '</td>';
				echo '</tr>';

				echo '<tr>';
					echo '<th align="left" valign="top">Session Data</th>';
					echo '<td>';
					foreach ($_SESSION as $key => $value) {
						echo '<div class="debuginfos_row">';
						if (is_object($value)) {
							echo '<div class="debuginfos_link_show_hide" onclick="showHide(\'debuginfos_elem_'.$key.'\');">'.$key.': Class ' . get_class($value) . ' <span class="debuginfos_arrows_more">&#8595; &#8595; &#8595;</span></div>';
							echo '<div class="debuginfos_elem" id="debuginfos_elem_'.$key.'">';
								echo var_export($value, true);
							echo '</div>';
						} else {
							echo $key . ': ' . var_export($value, true);
						}
						echo '</div>';
					}
					echo '</td>';
				echo '</tr>';
				
				echo '<tr>';
					echo '<th align="left" valign="top">Server Data</th>';
					echo '<td>';
					foreach ($_SERVER AS $key => $value) {
						echo '<div>' . $key . ': ' . var_export($value, true) . '</div>';
					}
					 echo '</td>';
				echo '</tr>';
			}
			
		echo '</table>';
		echo '</fieldset></pre>';
	}
}