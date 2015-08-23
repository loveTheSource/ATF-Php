<?php

namespace ATFApp\Core;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Core;

class PdoProfiler extends PdoDb {
	
	private $profiler = array();
	private $useProfiler = false;
	private $decimals = 6;
	private $connectionsCounter = 0;
	
	public function __construct($dsn, $username, $passwd, $options) {
		if (BasicFunctions::useProfiler()) {
			$this->useProfiler = true;
		}
		
		$this->connectionsCounter++;
		parent::__construct($dsn, $username, $passwd, $options);
	}
	
	public function addProfile($statement, $executionTime) {
		if (!isset($this->profiler[$statement])) {
			$this->profiler[$statement] = [];
		}
		$this->profiler[$statement][] = $executionTime;
	}

	public function prepare($statement, $driver_options=[]) {
		$profilerInUse = false;
		if ($this->useProfiler) {
			$profilerInUse = true;
			$before = microtime(true);
		}
		
		$result = parent::prepare($statement, $driver_options);
		
		if ($profilerInUse) {
			$executionTime = bcsub(microtime(true), $before, $this->decimals);
			if (!isset($this->profiler['PREPARE: ' . $statement])) {
				$this->profiler['PREPARE: ' . $statement] = array();
			}
			$this->profiler['PREPARE: ' . $statement][] = $executionTime;
		}
		
		return $result;
	}
	
	/**
	 * perform db query
	 * 
	 * @see \ATFApp\Core\PdoDb::query()
	 * @param string $statement (sql)
	 * @param boolean $ignoreCache
	 */
	public function query($statement) {
		$profilerInUse = false;
		if ($this->useProfiler) {
			$profilerInUse = true;
			$before = microtime(true);
		}
		
		$result = parent::query($statement);
		
		if ($profilerInUse) {
			$executionTime = bcsub(microtime(true), $before, $this->decimals);
			if (!isset($this->profiler['QUERY: ' . $statement])) {
				$this->profiler['QUERY: ' . $statement] = array();
			}
			$this->profiler['QUERY: ' . $statement][] = $executionTime;
		}
		
		return $result;
	}
	
	public function exec($statement) {
		$profilerInUse = false;
		if ($this->useProfiler) {
			$profilerInUse = true;
			$before = microtime(true);
		}
		
		$result = parent::exec($statement);
		
		if ($profilerInUse) {
			$executionTime = bcsub(microtime(true), $before, $this->decimals);
			if (!isset($this->profiler['EXEC: ' . $statement])) {
				$this->profiler['EXEC: ' . $statement] = array();
			}
			$this->profiler['EXEC: ' . $statement][] = $executionTime;
		}
		
		return $result;
	}
	
	public function getProfilerHtml($name="") {
		$html = '<style>
				table.atf_db_profiler_results tbody tr:nth-child(even) { background-color:#EEEEEE; }
				table.atf_db_profiler_results tbody tr:nth-child(odd) { background-color:#FAFAFA; }
				</style>';
		$html .= '<div style="width: 100%;font-size: 12px">';
		$html .= '<pre><fieldset>';
		$html .= '<legend onclick="var el=document.getElementById(\'db_profiler_results\'); if (el.style.display != \'none\') {el.style.display = \'none\'} else {el.style.display = \'inline\'}" style="cursor:pointer;">';
		$html .= '<b>D B - P R O F I L E R | </b>Connection: ' . $name . '</legend>';
		
		$html .= '<div id="db_profiler_results">';
			$html .= '<table class="atf_db_profiler_results">';
			$html .= '<tr>';
				$html .= '<th>DB Query</th><th>Execution Times</th>';
			$html .= '</tr>';
			foreach ($this->profiler AS $query => $profiles) {
				$html .= '<tr>';
					$html .= '<td>'.$query.'</td>';
					$html .= '<td>'.implode(', ', $profiles).'</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
			
			if (ProjectConstants::MODELS_QUERY_CACHE) {
				// models query cache
				$html .= '<table class="atf_db_profiler_results">';
					$cacheElems = Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE);
					$cacheElemsCount = (is_array($cacheElems)) ? count($cacheElems) : 0;
					$html .= '<tr><th>DB Connections</th><td>'.$this->connectionsCounter.'</td></tr>';
					$html .= '<tr><th>Cached Models/Queries</th><td>'.$cacheElemsCount.'</td></tr>';
					$html .= '<tr><th>Retrieved from Cache</th><td>' . (int)Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBALS_MODELS_QUERY_CACHE_COUNT) . '</td></tr>';
				$html .= '</table>';
				
			}
		$html .= '</div>';
		
		$html .= '</fieldset></pre>';
		$html .= '</div>';
		
		return $html;
		
	}
}