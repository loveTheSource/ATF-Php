<?php

namespace ATFApp\Helper;

use ATFApp\Core AS Core;

class Format {
	
	private static $stringNone = '--';
	
	public function __construct() { }
	
	/**
	 * format sql timestamp (depending on locale settings)
	 * known views: date, time, date_time, timeshort, date_timeshort, mysql
	 * 
	 * @param integer $timestamp
	 * @param string $view
	 * @return string
	 */
	public static function formatSqlTimestamp($timestamp, $view="date", $appendTimezone="") {
		if ($timestamp != "0000-00-00 00:00:00") {
			$unixTime = strtotime($timestamp);
			if ($unixTime) {
				return self::formatUnixTimestamp($unixTime, $view, $appendTimezone);
			}
		}
		return self::$stringNone;
	}
	
	/**
	 * format unix timestamp (depending on locale settings)
	 * known views: date, time, date_time, timeshort, mysql
	 * 
	 * @param inetger $timestamp
	 * @param string $view
	 * @param string $appendTimezone
	 * @return string
	 */
	public static function formatUnixTimestamp($timestamp=null, $view="date", $appendTimezone="") {
		if (is_null($timestamp)) {
			$timestamp = time();
		}
		if ($timestamp != "0") {
			$langObj = Core\Factory::getLangObj();
			if ($langObj->isLangText('basics', 'dateformat_'.$view)) {
				$dateFormat = $langObj->getLangText('basics', 'dateformat_'.$view);
				$formated = date($dateFormat, $timestamp);
				
				if ($formated) return $formated . $appendTimezone;
			}
		}
		return self::$stringNone . ' ' . $appendTimezone;
	}
	
	public static function formatCurrency($value, $currency="EUR") {
		switch ($currency) {
			case "€":
			case "EUR":
				// 100,00 EUR
				return self::formatDecimalSign($value) . ' ' . $currency;
				break;
			
			case "$":
			case "USD":
			default:
				// USD 100.00
				return $currency . ' ' . self::formatDecimalSign($value);
		}
	}
	
	/**
	 * cleanupText
	 * removes all illegal characters (e.g. for nice url names)
	 * 
	 * @param string $text
	 * @param boolean $removeSpaces
	 * @return string
	 */
	public static function cleanupText($text, $removeSpaces=true) {
		$chars2replace = array(
		    'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 
		    'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 
		    'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 
		    'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 
		    'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 
		    'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 
		    'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
		);
		
		$text = strtr($text, $chars2replace);
		$text = str_replace('&', '+', $text);  // replace '&'
	    $text = trim(preg_replace('/[^\w\d_ -]/si', '', $text)); //remove all illegal chars
	    
	    if ($removeSpaces) {
	    	$text = str_replace(' -', '-', $text);
	    	$text = str_replace('- ', '-', $text);
	    	$text = str_replace(' ', '-', $text);
	    }
	    
	    return $text;
	}
	
	/**
	 * nicely format some bytes
	 * 
	 * @param integer $bytes
	 * @param integer $precision
	 * @return string
	 */
	public static function formatBytes($bytes, $precision=2) {
		$result = $bytes;
		$bytes = floatval($bytes);
		$bytesTable = array(
			"PB" => 1024 * 1024 * 1024 * 1024 * 1024,
			"TB" => 1024 * 1024 * 1024 * 1024,
			"GB" => 1024 * 1024 * 1024,
			"MB" => 1024 * 1024,
			"KB" => 1024,
			"B"  => 1,
		);
		
		foreach($bytesTable as $unit => $b) {
			if($bytes >= $b) {
				$result = round($bytes / $b, $precision);
				
				$result = self::formatDecimalSign($result) . " " . $unit;
				break;
			}
		}
		return $result;
	}
	
	/**
	 * 
	 * @param float $value
	 * @return string
	 */
	public static function formatDecimalSign($value) {
		$langObj = Core\Factory::getLangObj();
		$separator = $langObj->getLangText('basics', 'decimal_separator');
		if ($separator != ".") {
			return str_replace(".", $separator, strval($value));
		}
		return $value;
	}
}