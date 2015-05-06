<?php

namespace ATFApp\Helper;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

/**
 * Helper\Validator
 * 
 * validates given values against rulesets
 * 
 * all static checker methods return true/false
 */

class Validator {
	
	private $rulesSeparator = ':';
	private $errors = array();
	
	public function __construct() { }

	/**
	 * validate multiple values againt multiple rules
	 * 
	 * usage:
	 * $values = array('value1' => 'some text', 'value2' => 123);
	 * $methods = array('value1' => array('required', 'minlen:2'), 'value2' => array('int'));
	 * $validator->validate($data, $methods);
	 * 
	 * will call checkRequired + checkMinlen for value1 as well as checkInt for value2
	 * on return false the errors are available via $validator->getErrors();
	 * 
	 * @param array $data 
	 * @param array $methods
	 */
	public function validate(Array $values, Array $methods) {
		// start fresh (delete all previously existing errors)
		$this->errors = array();
		$valid = true;
		
		if (count($values) != count($methods)) {
			throw new Exceptions\Custom("Validator: values and methods must have equal elements", null, null, array('values'=>$values, 'methods'=>$methods));
		}
		foreach ($methods AS $itemId => $rulesset) {
			if (!array_key_exists($itemId, $values)) {
				throw new Exceptions\Custom("Validator: values and methods mismatch", null, null, array('values'=>$values, 'methods'=>$methods));
			}
			
			if (!in_array('required', $rulesset) && $values[$itemId] == '') {
				// if not required, then dont check other methods in case field was left empty
			} else {
				// either required or value given
				foreach ($rulesset AS $rule) {
					if (strpos($rule, $this->rulesSeparator) !== false) {
						$parts = explode($this->getSeparator(), $rule);
						$function = trim($parts[0]);
						$checkData = trim($parts[1]);
					} else {
						$function = trim($rule);
						$checkData = null;
					}
				
					$checkFunction = 'check' . ucfirst($function);
					if (method_exists($this, $checkFunction)) {
						if ($this->$checkFunction($values[$itemId], $checkData) === false) {
							if (!isset($this->errors[$itemId])) {
								$this->errors[$itemId] = array();
							}
				
							$this->errors[$itemId][] = array('check' => $function, 'data' => $checkData);
				
							$valid = false;
						}
					}
				}
			}
		}
		
		return $valid;
	}
	
	/**
	 * get errors
	 * 
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	/**
	 * set rules separator character
	 * 
	 * @param string $character
	 */
	public function setSeparator($character) {
		$this->rulesSeparator = $character;
	}
	
	/**
	 * get rules separator character
	 * 
	 * @return string
	 */
	public function getSeparator() {
		return $this->rulesSeparator;
	}
	
	######################
	####### basics #######
	######################
	
	
	/**
	 * not empty
	 * 
	 * @param unknown $value
	 * @return boolean
	 */
	public static function checkRequired($value) {
		if ($value === '' || $value === null) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * is empty
	 * 
	 * @param unknown $value
	 * @return boolean
	 */
	public static function checkEmpty($value) {
		return !self::checkRequired($value);
	}
	
	
	#######################
	####### numbers #######
	#######################
	
	/**
	 * integer positive and negative
	 * 
	 * @param integer $value
	 * @return boolean
	 */
	public static function checkInt($value) {
		$regexp = "/^-?[0-9]+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * integer (only positive)
	 * 
	 * @param integer $value
	 * @return boolean
	 */
	public static function checkIntpositive($value) {
		$regexp = "/^[0-9]+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * integer negative
	 * 
	 * @param integer $value
	 * @return boolean
	 */
	public static function checkIntnegative($value) {
		$regexp = "/^-[0-9]+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * integer larger than
	 *
	 * @param integer $value
	 * @return boolean
	 */
	public static function checkIntmin($value, $min) {
		$regexp = "/^-?[0-9]+$/";
		if (preg_match($regexp, $value) && $value >= $min) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * integer smaller than
	 *
	 * @param integer $value
	 * @return boolean
	 */
	public static function checkIntmax($value, $max) {
		$regexp = "/^-?[0-9]+$/";
		if (preg_match($regexp, $value) && $value <= $max) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * is float
	 * 
	 * @param float $value
	 * @return boolean
	 */
	public static function checkFloat($value) {
		$regexp = "/^-?[0-9]+[\.[0-9]+]?$/";
		if (preg_match($regexp, $value) || self::checkInt($value)) {
			return true;
		} else {
			return false;
		}
	}
	public static function checkFloatpositive($value) {
		$regexp = "/^[0-9]+[\.[0-9]+]?$/";
		if (preg_match($regexp, $value) || self::checkIntpositive($value)) {
		#if (is_float($value) && $value >= 0) {
			return true;
		} else {
			var_dump($value);
			return false;
		}
	}
	public static function checkFloatnegative($value) {
		$regexp = "/^-[0-9]+[\.[0-9]+]?$/";
		if (preg_match($regexp, $value) || self::checkIntnegative($value)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	#######################
	####### strings #######
	#######################
	
	/**
	 * minimum length 
	 * 
	 * @param string $value
	 * @param integer $min
	 * @return boolean
	 */
	public static function checkMinlen($value, $min) {
		if (strlen($value) >= $min) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * maximum length
	 * 
	 * @param strung $value
	 * @param integer $max
	 * @return boolean
	 */
	public static function checkMaxlen($value, $max) {
		if (strlen($value) <= $max) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * basic email validation
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public static function checkEmail($value) {
		if (strlen($value) <= 256 && filter_var($value, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * basic url validation
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public static function checkUrl($value) {
		$regexp = "/^(http:\/\/|https:\/\/)(w{3}\.)?([a-zA-Z0-9-\.]{2,})(\.)([a-zA-Z]{2,4})+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * phone number 
	 * (numbers +, -, /)
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public static function checkPhone($value) {
		$regexp = "/^[0-9 \- \+ \/]+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * letters and numbers only
	 * 
	 * a-z A-Z 0-9 _ (underscore)
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public static function checkAlphanum($value) {
		$regexp = "/^[\w]+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * check for letters, numbers and some special chars:
	 * 
	 * a-z A-Z 0-9 _ [space]
	 * ä ö ü Ä Ö Ü ß
	 * á à â é è ê í ì î ó ò ô ú ù û
	 * Á À Â É È Ê Í Ì Î Ó Ò Ô Ú Ù Û
	 * ! = & "   (not escaped)
	 * - ( ) [ ] { } ? * + . , : ; / \ '   (escaped)
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public static function checkFreetext($value) {
		$regexp = '/^[\w\säöüÄÖÜßáàâéèêíìîóòôúùûÁÀÂÉÈÊÍÌÎÓÒÔÚÙÛ!=&"\-\(\)\[\]\{\}\?\*\+\.\,\:\;\/\\\']+$/';
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * letters + numbers + special letters
	 * 
	 * a-z A-Z 0-9 - _ [space]
	 * ä ö ü Ä Ö Ü ß
	 * á à â é è ê í ì î ó ò ô ú ù û
	 * Á À Â É È Ê Í Ì Î Ó Ò Ô Ú Ù Û
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public static function checkLatinplus($value) {
		$regexp = "/^[\w-äöüÄÖÜßáàâéèêíìîóòôúùûÁÀÂÉÈÊÍÌÎÓÒÔÚÙÛ\s]+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * latin letters plus space
	 * (no numbers)
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public static function checkLatin($value) {
		$regexp = "/^[\w\s\D]+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	######################
	####### custom #######
	######################
		
	/**
	 * street and no
	 * 
	 * @param string $value
	 * @return boolean
	 */
	public static function checkStreet($value) {
		$regexp = "/^[\wäöüÄÖÜßáàâéèêíìîóòôúùûÁÀÂÉÈÊÍÌÎÓÒÔÚÙÛ.-\s]+$/";
		if (preg_match($regexp, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * check username
	 * 
	 * 0-9 a-z A-Z _ - [Space]
	 * 
	 * @param unknown $value
	 * @return boolean
	 */
	public static function checkUsername($value) {
		$regexp = "/^[\w\s-]+$/";
		if (preg_match($regexp, $value) && strlen($value) >= 4) {
			return true;
		} else {
			return false;
		}
	}
	


}

?>