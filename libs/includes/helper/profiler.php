<?php

namespace ATFApp\Helper;

/**
 * simple profiler
 * (independent from any other classes)
 * 
 * usage:
 * require_once('profiler.php');
 * declare(ticks=1);
 * HelperProfiler::startProfiler();
 * ...
 * HelperProfiler::stopProfiler();
 * $profile = HelperProfiler::getProfile();
 * $overhead = HelperProfiler::getOverhead();
 * or
 * $profile = HelperProfiler::getProfileHtml();
 */
class Profiler {
	
	protected static $profile = [];					// profile data
	protected static $lastTick = 0;					// last tick
	protected static $timeBeforeProfiler = 0;		// time of code execution before profiler
	protected static $overhead = 0;					// record overhead
	protected static $counter = 0;					// ticks counter
	protected static $decimals = 6;					// decimals used in BCMath functions
	protected static $stripParams = 100;				// strip params after charactcters
	protected static $ticksPercentageInfo = 2;		// info for functions where ticks > percentage of all ticks
	protected static $ticksPercentageWarning = 4;	// warning for functions where ticks > percentage of all ticks
	protected static $timePercentageInfo = 4;		// info for functions where time > percentage of complete time
	protected static $timePercentageWarning = 6;	// warning for functions where time > percentage of complete time
	protected static $includeFunctions = [			// include/require functions
		'require', 
		'require_once', 
		'include', 
		'include_once'
	];
	
	// only static use
	private function __construct() { }

	/**
	 * start profiling
	 * 
	 * by registering tick function
	 */
	public static function startProfiler() {
		$before = microtime(true);
		
		self::$lastTick = microtime(true);
		self::$timeBeforeProfiler = bcsub(microtime(true), $_SERVER["REQUEST_TIME_FLOAT"], self::$decimals);
		
		register_tick_function([__CLASS__, 'profileTick'], false);
	
		self::$overhead += microtime(true) - $before;
	}
	
	/**
	 * stop profiling
	 * 
	 * by unregistering tick function
	 */
	public static function stopProfiler() {
		unregister_tick_function ([__CLASS__, 'profileTick']);
	}
	
	
	/**
	 * is profiler running
	 * 
	 * @return boolean
	 */
	public static function isActive() {
		return self::$lastTick != 0;
	}

	/**
	 * return profiler overhead
	 *
	 * @return float
	 */
	public static function getOverhead() {
		return self::$overhead;
	}
	
	/**
	 * return collected profile information
	 * 
	 * @return array
	 */
	public static function getProfile() {
		return self::$profile;
	}
	
	/**
	 * return collected profile information as HTML
	 * 
	 * @return string
	 */
	public static function getProfileHtml() {
		$before = microtime(true);
		
		$profileTotalTime = 0;
		$counter = 0;
		
		$html = '<style>
				table#profiler_results tbody tr:nth-child(even) { background-color:#EEEEEE; }
				table#profiler_results tbody tr:nth-child(odd) { background-color:#FAFAFA; }
				table#profiler_results tbody td.info { background-color:#fff697; }
				table#profiler_results tbody td.warning { background-color:#ff9494; }
				</style>';
		$html .= '<script type="text/javascript" src="/js/TableSort.js"></script>';
		$html .= '<div style="width: 100%;font-size: 12px">';
		$html .= '<pre><fieldset>';
		$html .= '<legend onclick="var el=document.getElementById(\'profiler_results\'); if (el.style.display != \'none\') {el.style.display = \'none\'} else {el.style.display = \'inline\'}" style="cursor:pointer;">';
		$html .= '<b>P R O F I L E R</b></legend>';
		
		$html .= '<table id="profiler_results">';
		$html .= '<thead><tr align="left">';
			$html .= '<th class="sortierbar">File</th>';
			$html .= '<th class="sortierbar">Function</th>';
			$html .= '<th class="sortierbar">Ticks</th>';
			$html .= '<th class="sortierbar">Total</th>';
			$html .= '<th>Arguments</th>';
		$html .= '</tr></thead>';
		$html .= '<tbody>';
		$maxTime = 0;
		$maxTicks = 0;
		$maxTicksTrace = "";
		$maxTimeTrace = "";
		$functionCounter = 0;
		foreach (self::$profile as $file => $data) {
			foreach ($data as $func => $functionsData) {
				$functionCounter++;
				$profileTotalTime = bcadd($profileTotalTime, $functionsData['time'], self::$decimals);
				
				if (bccomp($functionsData['ticks'], $maxTicks, self::$decimals) == 1) {
					$maxTicks = $functionsData['ticks'];
					$maxTicksTrace = $func;
				}
				
				if (bccomp($functionsData['time'], $maxTime, self::$decimals) == 1) {
					$maxTime = $functionsData['time'];
					$maxTimeTrace = $func;
				}
			}
		}
		foreach (self::$profile as $file => $data) {
			foreach ($data as $func => $functionsData) {
				$html .= '<tr>';
					$html .= '<td>' . $file. '</td>';
					
					$html .= '<td>' . $func. '</td>';
					
					$ticksPercentage = bcdiv($functionsData['ticks'], bcdiv(self::$counter, 100, self::$decimals), self::$decimals);
					$ticksClass = '';
					if (bccomp($ticksPercentage, self::$ticksPercentageWarning, self::$decimals) > -1) {
						$ticksClass = 'warning';
					} elseif (bccomp($ticksPercentage, self::$ticksPercentageInfo, self::$decimals) > -1) {
						$ticksClass = 'info';
					}
					$html .= '<td class="' . $ticksClass . '">' . $functionsData['ticks']. '</td>';
					
					$timePercentage = bcdiv($functionsData['time'], bcdiv($profileTotalTime, 100, self::$decimals), self::$decimals);
					$timeClass = '';
					if (bccomp($timePercentage, self::$timePercentageWarning, self::$decimals) > -1) {
						$timeClass = 'warning';
					} elseif (bccomp($timePercentage, self::$timePercentageInfo, self::$decimals) > -1) {
						$timeClass = 'info';
					}
					$html .= '<td class="' . $timeClass . '">' . $functionsData['time']. '</td>';
					
					$html .= '<td>' . $functionsData['args']. '</td>';
				$html .= '</tr>';
			}
		}
		$html .= '</table>';
		
		$html .= '<table>';
		$html .= '<tr align="left"><th>Profiled Files</th><td>' . count(self::$profile) . '</td></tr>';
		$html .= '<tr align="left"><th>Profiled Functions</th><td>' . $functionCounter . '</td></tr>';
		$html .= '<tr align="left"><th>Total Ticks Recorded</th><td>' . self::$counter . '</td></tr>';
		$html .= '<tr align="left"><th>Max Ticks</th><td>' . $maxTicks . ' (' . $maxTicksTrace . ')</td></tr>';
		$html .= '<tr align="left"><th>Longest Call</th><td>' . $maxTime . ' sec (' . $maxTimeTrace . ')</td></tr>';
		$html .= '<tr align="left"><th>Time Profiled</th><td>' . $profileTotalTime . ' sec</td></tr>';
		$html .= '<tr align="left"><th>Before Profiler</th><td>' . self::$timeBeforeProfiler . ' sec</td></tr>';
		
		// save overhead
		$overhead = bcsub(microtime(true), $before, self::$decimals);
		self::$overhead = bcadd(self::$overhead, $overhead, self::$decimals);
		$html .= '<tr align="left"><th>Profiler Overhead</th><td>' . self::getOverhead() . ' sec</td></tr>';
		$html .= '</tbody></table>';
		
		$html .= '</fieldset></pre>';
		$html .= '</div>';
	
		$html .= '<script type="text/javascript">
				var profilerTable = document.getElementById("profiler_results");
				new JB_Table(profilerTable);
				</script>';
		
		return $html;
	}
	
	/**
	 * record tick
	 * 
	 * save profile data to self::profiles
	 */
	public static function profileTick() {
		$before = microtime(true);
		
        if (version_compare(PHP_VERSION, '5.3.6', '<' )) {
            $trace = debug_backtrace(true);
        } elseif (version_compare(PHP_VERSION, '5.4.0', '<' )) {
			#DEBUG_BACKTRACE_IGNORE_ARGS
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        } else {
            // backtrace with limit (php >= 5.4)
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        }

		// get second step
        if (count($trace) >= 2) {
            $step = $trace[1];
        }

		// get closure file (before deleting trace)
		$closureFile = @$trace[0]['file'];
        // free memory
        unset($trace);

		// determine file
        if (in_array(strtolower($step['function']), self::$includeFunctions)) {
            $file = $step['file'];
        } elseif (isset($step['object']) && method_exists($step['object'], $step['function'])) {
            try {
                $reflection = new \ReflectionMethod($step['object'], $step['function']);
                $file = $reflection->getFileName();
            } catch (Exception $e) {
				throw $e;
            }
        } elseif (isset($step['class']) && method_exists($step['class'], $step['function'])) {
            try {
                $reflection = new \ReflectionMethod($step['class'], $step['function']);
                $file = $reflection->getFileName();
            } catch (Exception $e) {
				throw $e;
            }
        } elseif (!empty($step['function']) && function_exists($step['function']) ) {
            try {
                $reflection = new \ReflectionFunction($step['function']);
                $file = $reflection->getFileName();
            } catch (Exception $e) {
				throw $e;
            }
        } elseif ('__lambda_func' == $step['function'] || '{closure}' == $step['function']) {
            $file = preg_replace('/\(\d+\)\s+:\s+runtime-created function/', '', $closureFile);
        } elseif (isset($step['file'])) {
            $file = $step['file'];
        } else {
            $file = 'n/a - scriptname: ' . $_SERVER['SCRIPT_FILENAME'];
        }
		
		$args = '';
		if (self::$counter >= 100 && array_key_exists('args', $step) && is_array($step['args']) && !empty($step['args'])) {
			foreach ($step['args'] as $a) {
				if ($args != '') {
					$args .= ", ";
				}
				if (is_array($a) || is_object($a)) {
					$args .= var_export($a, true);
				} else {
					$args .= $a;
				}
			}
			$args = preg_replace('/[\s]{2,}|[\t\n]/', '', $args);
			$args = substr(htmlspecialchars($args), 0, self::$stripParams);
		}

		// count tick (needs to be called after searching the file)
		self::$counter++;
		
        $function = $step['function'];
		if (in_array($function, self::$includeFunctions)) {
			$function = $step['function'] . ' (Line: ' . $step['line'] . ')';
		} elseif (isset($step['object'])) {
			$type = (!empty($step['type'])) ? $step['type'] : '::';
            $function = get_class($step['object']) . $type . $step['function'];
        } elseif (isset($step['class'])) {
			$type = (!empty($step['type'])) ? $step['type'] : '::';
			$function = $step['class'] . $type . $step['function'];
		}
		
		// save profile
        if (!isset(self::$profile[$file])) {
            self::$profile[$file] = [];
        }
        if (!isset(self::$profile[$file][$function])) {
            self::$profile[$file][$function] = [
				'time' => 0,
				'ticks' => 0,
				'args' => $args
			];
        }
		self::$profile[$file][$function]['ticks']++;
        self::$profile[$file][$function]['time'] = bcadd(self::$profile[$file][$function]['time'], bcsub($before, self::$lastTick, self::$decimals), self::$decimals);
 
		// save overhead
		$now = microtime(true);
		$overhead = bcsub($now, $before, self::$decimals);
		self::$overhead = bcadd(self::$overhead, $overhead, self::$decimals);
		// save current time
		self::$lastTick = $now;
    }
}