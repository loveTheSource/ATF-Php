<?php

namespace ATFApp\Helper;

use ATFApp\Core AS Core;

class Format {
	
	private static $stringNone = '--';
    // only number, characters and dash (-)
    private static $filenameAllowedCharacters = '/[^\w\d\-]/';
    // only number, characters, dash (-) and dot (.)
    private static $filenameAllowedCharactersDots = '/[^\w\d\-\.]/';
	
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
	 * cleanup text:
	 * 
	 * characters, numbers, space,
	 * _-+=*&()<>\.,:;!?"'#
	 * characters with acents etc
	 * 
	 * @param string $text
	 * @return string
	 */
	public static function cleanupTextPlus($text) {
		// no idea why this char (§) causes problems, when not deleted before...
		$text = str_replace('§', '', $text);
		$allowedChars = '/[^\w\d\s\_\-\+\=\*\&\(\)\<\>\/\.\,\:\;\!\?\"\'\#ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïñòóôõöøùúûüýÿĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĹĺĻļĽľĿŀŁłŃńŅņŇňŉŌōŎŏŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžſƒƠơƯưǍǎǏǐǑǒǓǔǕǖǗǘǙǚǛǜǺǻǼǽǾǿ ]/si';
		$text = trim(preg_replace($allowedChars, '', $text));
		return $text;
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
		$text = self::replaceSpecialChars($text);

		$text = str_replace('&', '+', $text);  // replace '&'
	    $text = trim(preg_replace('/[^\w\d\_ \-\+]/si', '', $text)); //remove all illegal chars
	    
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
		$bytesTable = [
			"PB" => 1024 * 1024 * 1024 * 1024 * 1024,
			"TB" => 1024 * 1024 * 1024 * 1024,
			"GB" => 1024 * 1024 * 1024,
			"MB" => 1024 * 1024,
			"KB" => 1024,
			"B"  => 1,
		];
		
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


	    /**
     * remove non whitelist characters
     * 
     * @param string $filename
     * @param boolean $dotsAllowed allow dots (for filename with extensions)
     * @param string $replaceWith character to replace with
     * @return string
     */
    public static function cleanupFilename($filename, $dotsAllowed=false, $replaceWith='') {
        $filename = self::replaceSpecialChars($filename);
        
        if ($dotsAllowed) {
            return preg_replace(self::$filenameAllowedCharactersDots , $replaceWith, $filename);
        } else {
            return preg_replace(self::$filenameAllowedCharacters , $replaceWith, $filename);
        }
    }


    /**
     * replace things like accents, umlauts and other special chars
	 * 
	 * @param string $str
	 * @return string
     */
    public static function replaceSpecialChars($str) {
      $a = [
          'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 
          'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 
          'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 
          'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 
          'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 
          'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 
          'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 
          'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 
          'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 
          'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 
          'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 
          'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 
          'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 
          'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 
          'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 
          'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 
          'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 
          'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 
          'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 
          'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 
          'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 
          'ǿ'];

      $b = [
          'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 
          'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 
          'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 
          'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 
          'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 
          'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 
          'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 
          'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 
          'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 
          'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 
          'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 
          'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 
          'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 
          'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 
          'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 
          'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 
          'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 
          'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 
          'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 
          'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 
          'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 
          'o'];
      
      
      return str_replace($a, $b, $str);
    }

}