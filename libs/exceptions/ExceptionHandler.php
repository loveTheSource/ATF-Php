<?php

namespace ATFApp\Exceptions;

use ATFApp\Exceptions AS Exceptions;

require_once 'CustomException.php';
require_once 'CoreException.php';
require_once 'DbException.php';

/**
 * ExceptionHandler
 * 
 * requires ENVIRONMENT constant to be defined (values: debug | staging | live)
 * used as singleton to make sure everything has to be defined only once (e.g. e-mail)
 * 
 * @author christian bacherer
 *
 */
class ExceptionHandler {
	
	private $logfile = "./exceptionHandler.log"; 	// default path to the logfile
	
	private static $recipients = array(); 					// array e-mail recipients
	private static $instance = null;
	
	public $appEnvironment = "live";  // default environment (if nothing else defined)
	
	private function __construct() {
		if (defined('ENVIRONMENT')) {
			$this->appEnvironment = ENVIRONMENT;
		}
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public static function handle(\Exception $e) {
		$handler = self::getInstance();

		// define reaction pattern
		switch ($handler->appEnvironment) {
			case "debug":
				$handler->display($e);
				break;
				
			case "staging":
				$handler->display($e);
				#$handler->logToFile($e);
				break;
				
			case "live":
				#$handler->logToFile($e);
				$handler->send($e);
				break;
		}
	}
	
	/**
	 * set email recipients
	 * 
	 * @param array $email
	 */
	public static function setEmailRecipients(Array $emails) {
		self::$recipients = $emails;
	}
	/**
	 * add email recipient
	 * 
	 * @param string $email
	 */
	public static function addEmailRecipient($email) {
		self::$recipients[] = $email;
	}
	/**
	 * get email recipient
	 * 
	 * @return string
	 */
	public static function getEmailRecipients() {
		return self::$recipients;
	}
	
	/**
	 * set logfile
	 * 
	 * @param string $logfile
	 */
	public function setLogfile($logfile) {
		$this->logfile = $logfile;
	}
	/**
	 * get logfile
	 * 
	 * @return string
	 */
	public function getLogfile() {
		return $this->logfile;
	}
	
	/**
	 * send exception data via email
	 * 
	 * @param string $addressTo
	 * @param string $addressFrom
	 * @param string $subjectPrefix
	 * @param string $subjectSuffix
	 */
	public function send(\Exception $e, $addressFrom = null, $subjectPrefix = "", $subjectSuffix = "") {
		$recipients = $this->getEmailRecipients();
		
		$subject = "";
		if (is_string($subjectPrefix)) $subject .= $subjectPrefix;
		$subject .= get_class($e) . " @" . $_SERVER['SERVER_NAME'] . ": " . $e->getMessage();
		if (is_string($subjectSuffix)) $subject .= $subjectSuffix;
		
		$message = $this->getAll($e);
		
		$header = (!is_null($addressFrom)) ? "From: " . $addressFrom : "";
		
		if (is_array($recipients) && count($recipients) >= 1) {
			foreach ($recipients AS $to) {
				@mail($to, $subject, $message, $header);
			}
		} else {
			$this->logToFile($e);
		}
	}
	
	/**
	 * display exception data
	 */
	public function display(\Exception $e, $die = false) {
		http_response_code(500);
		$data = $this->getAll($e);
		echo '<pre>' . $data . '</pre>';
		if ($die) die();
	}
	
	/**
	 * write exception log to file
	 */
	public function logToFile(\Exception $e, $file = null) {
		// TODO log to file code
		if (is_file($file) && is_writeable($file)) {
			$data = $this->getAll($e);
		}
		$data = $this->getAll($e);
	}
	
	/**
	 * get all exception data as string
	 * 
	 * @return string
	 */
	protected function getAll(\Exception $e) {
		$message = "Exception: " . get_class($e);
		$message .= "\nServer: " . $_SERVER['SERVER_NAME'] . " (" . $_SERVER['SERVER_ADDR'] . ")";
		$message .= "\nRemote IP: " . $_SERVER['REMOTE_ADDR'];
		$message .= "\nURL: " . $_SERVER['SERVER_NAME'];
		if ($_SERVER['SERVER_PORT'] != 80) $message .= ":" . $_SERVER['SERVER_PORT'];
		$message .= $_SERVER['REQUEST_URI'];
		$message .= "\nTimestamp: " . date("Y-m-d H:i:s") . " (Unix: " . time() . ")";
		$message .= $this->getExceptionAsString($e);
		
		if (method_exists($e, 'getAdditionalData') && !is_null($e->getAdditionalData())) {
			$message .= "\n\nAdditional data: \n" . print_r($e->getAdditionalData(), true);
		}
		
		$message .= "\n\n================================\n\n\$_GET:\n";
		if (!empty($_GET)) {
			$message .= print_r($_GET, true);
		} else {
			$message .= " - no get data - ";
		}
				
		$message .= "\n\n================================\n\n\$_POST:\n";
		if (!empty($_POST)) {
			$message .= print_r($_POST, true);
		} else {
			$message .= " - no post data - ";
		}
				
		$message .= "\n\n================================\n\n\$_SESSION:\n";
		if (session_id() != '') {
			if (!empty($_SESSION)) {
				$message .= print_r($_SESSION, true);
			} else {
				$message .= " - no session data - ";
			}
		} else {
			$message .= " - session not started - ";
		}
		
		$message .= "\n\n================================\n\n\$_COOKIE:\n";
		$message .= print_r($_COOKIE, true);
		
		$message .= "\n\n================================\n\n\$_SERVER:\n";
		$message .= print_r($_SERVER, true);
		
		return $message;
	}
	
	/**
	 * returns exception data as string
	 * 
	 * @param Exception $e
	 * @return string
	 */
	protected function getExceptionAsString(\Exception $e) {
		$exceptionString = "\nFile: " . $e->getFile();
		$exceptionString .= "\nLine: " . $e->getLine();
		$exceptionString .= "\nMessage: " . $e->getMessage();
		$exceptionString .= "\nCode: " . $e->getCode();
		$exceptionString .= "\n\nBacktrace: \n" . $e->getTraceAsString();
		
		if ($e !== null && method_exists($e, 'getPrevious') && $e->getPrevious() !== null) {
			$exceptionString .= "\n---------------------------------------\n";
			$exceptionString .= "\nPrevious: \n" . $this->getExceptionAsString($e->getPrevious());
		}
		
		return $exceptionString;
	}
}


// custom exception/error handling and setting the error level/visibility (maybe move elsewhere)

// other errors
function customErrorHandling($errNo, $errMsg, $fileName, $lineNum, $vars=array()) {
	$msg = "ErrorHandling: " . $errMsg . ' in file ' . $fileName . '(' . $lineNum . ')';
	$exception = new Custom($msg, $errNo, null, $vars);
	ExceptionHandler::handle($exception);
	exit();
}
// set error handler to function above
set_error_handler("ATFApp\Exceptions\customErrorHandling");



// fatal errors
function customShutdown() {
	$error = error_get_last();
	# check for fatal error
	#if($error && is_array($error) && ($error['type'] === E_ERROR)) {
	if (is_array($error) && array_key_exists('message', $error) && array_key_exists('file', $error) && array_key_exists('line', $error)) {
		// TODO handle fatal errors here
		$msg = "Shutdown: " . $error['message'] . ' in file ' . $error['file'] . '(' . $error['line'] . ')';
		$e = new Custom($msg, null, null, $error);
		ExceptionHandler::handle($e);
	}
	exit();
}
// set shutdown function
register_shutdown_function('ATFApp\Exceptions\customShutdown');


// set php error level
error_reporting(E_ALL);
ini_set('display_errors', 'Off');

