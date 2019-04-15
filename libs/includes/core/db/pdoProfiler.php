<?php

namespace ATFApp\Core\Db;

use ATFApp\BasicFunctions;
use ATFApp\ProjectConstants;
use ATFApp\Core;

class PdoProfiler extends PdoDb {
	
	private $profiler = [];
	private $useProfiler = false;
	private $decimals = 6;
	private $connectionsCounter = 0;
	
	/**
	 * constructor
	 * 
	 * @param string $dsn
	 * @param string $username
	 * @param string $passwd
	 * @param array $options
	 */
	public function __construct($dsn, $username, $passwd, $options = null) {
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

	/**
	 * prepare statement
	 * 
	 * @param string $statement
	 * @param array $options
	 * @return \PDOStatement
	 */
	public function prepare($statement, $options=[]) {
		$profilerInUse = false;
		if ($this->useProfiler) {
			$profilerInUse = true;
			$before = microtime(true);
		}
		
		$result = parent::prepare($statement, $options);
		
		if ($profilerInUse) {
			$executionTime = bcsub(microtime(true), $before, $this->decimals);
			if (!isset($this->profiler['PREPARE: ' . $statement])) {
				$this->profiler['PREPARE: ' . $statement] = [];
			}
			$this->profiler['PREPARE: ' . $statement][] = $executionTime;
		}
		
		return $result;
	}
	
	/**
	 * perform db query
	 * 
	 * @see \ATFApp\Core\Db\PdoDb::query()
	 * @param string $statement (sql)
	 * @return \PDOStatement|false
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
				$this->profiler['QUERY: ' . $statement] = [];
			}
			$this->profiler['QUERY: ' . $statement][] = $executionTime;
		}
		
		return $result;
	}
	
	/**
	 * exec

	 * @param string $statement (sql)
	 * @return int|false
	 */
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
				$this->profiler['EXEC: ' . $statement] = [];
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
		$html .= '<fieldset style="font-family: monospace; margin-bottom: 10px;">';
		$html .= '<legend onclick="var el=document.getElementById(\'db_profiler_results\'); if (el.style.display != \'none\') {el.style.display = \'none\'} else {el.style.display = \'inline\'}" style="cursor:pointer;">';
		$html .= '<b>D B - P R O F I L E R | </b>Connection: ' . $name . '</legend>';
		
		$html .= '<div id="db_profiler_results">';
			$html .= '<table class="atf_db_profiler_results sortierbar">';
			$html .= '<tr>';
				$html .= '<th class="sortierbar">DB Query</th><th class="sortierbar">Execution Times</th>';
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
		
		$html .= '</fieldset>';
		$html .= '</div>';
		
		return $html;
		
	}
}