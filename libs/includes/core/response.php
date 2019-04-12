<?php

namespace ATFApp\Core;

use ATFApp\Exceptions;

class Response {
	
	private static $instance = null;

	private $statusCodeDefault = 200;  // has to exist in the $statusCodesMap array
	private $statusCodeString = null;
	// some common status codes and their description
	private $statusCodesMap = [
		200 => 'OK',
		201 => 'Created',
		303 => 'See Other',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		400 => 'Bad Request',
		403 => 'Forbidden',
		404 => 'Not Found',
		418 => 'I’m a teapot',  // :)
		500 => 'Internal Server Error',
	];
	private $responseHeaders = [];
	
	// private to force singleton
	private function __construct() {
		$this->statusCodeString = $this->setStatusCode($this->statusCodeDefault);
	}
	
	/**
	 * get object instance (singleton)
	 * 
	 * @return \ATFApp\Core\Response
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}




	/**
	 * set the response status code e.g. 200
	 * will be overwritten each time it is called
	 * 
	 * @param integer $code
	 * @param string $descr
	 */
	public function setStatusCode($code, $descr=null) {
		if (is_null($descr)) {
			$value = $this->getCodeValue($code);
		}
		$status = $code . ' ' .$descr;
		
		$sapiType = php_sapi_name();
		if ($sapiType != 'cgi-fcgi') {
			$this->statusCodeString = "HTTP/1.0 " . $status;
		} else {
			// fast cgi requires different header for status
			$this->statusCodeString = "Status: " . $status;
		}
	}

	/**
	 * add additional header
	 * 
	 * @param string $name
	 * @param string $value
	 */
	public function addHeader($name, $value) {
		$this->responseHeaders[$name] = $value;
	}
	public function headerExists($name) {
		return array_key_exists($name, $this->responseHeaders);
	}
	
	/**
	 * send header Location: ...
	 * 
	 * @param string $url
	 * @param boolean $exit
	 */
	public function respondRedirect($url, $exit=true) {
		$this->addHeader("Location", $url);
		$this->respond(null, $exit);
	}
	
	/**
	 * send HTML response
	 * 
	 * @param string $html
	 */
	public function respondHtml($html, $exit=true) {
		$this->respond($html, $exit);
	}
	
	/**
	 * set headers and send json response
	 * 
	 * @param array $data
	 * @param integer $cache
	 */
	public function respondJson($data, $cache=null, $exit=true) {
		if (!is_null($cache)) {
			$expire = gmdate("D, d M Y H:i:s", time() + (int)$cache);
		} else {
			$this->addHeader('Cache-Control', 'no-cache, must-revalidate');
			// expires 60 min in the past
			$expire = gmdate("D, d M Y H:i:s", time() - 3600);
		}
		$this->addHeader('Expires', $expire . ' GMT');
		$this->addHeader('Content-Type', 'application/json');
		
		$json = json_encode($data);
		if ($json) {
			$this->respond($json, $exit);
		} else {
			$this->setStatusCode(400);
			$this->respond(null, $exit);
		}
	}
	
	/**
	 * set headers and send xml response
	 * 
	 * @param string $data
	 * @param integer $cache
	 */
	// TODO test this method...
	public function respondXml($data, $cache=null, $exit=true) {
		if (!is_null($cache)) {
			$expire = gmdate("D, d M Y H:i:s", time() + (int)$cache);
		} else {
			$this->addHeader('Cache-Control', 'no-cache, must-revalidate');
			// expires 60 min in the past
			$expire = gmdate("D, d M Y H:i:s", time() - 3600);
		}
		$this->addHeader('Expires', $expire . ' GMT');
		$this->addHeader('Content-Type', 'application/xml; charset=utf-8');
		
		$this->respond($data, $exit);
	}
	
	/**
	 * send response (headers and content)
	 * 
	 * @param string $string
	 */
	private function respond($responseString=null, $exit=true) {
		if (!headers_sent()) {
			header($this->statusCodeString);
			foreach ($this->responseHeaders AS $name => $value) {
				header($name . ': ' . $value);
			}
				
			if (!is_null($responseString)) {
				echo $responseString;
			}

			if ($exit) exit();
		} else {
			$exceptionData = [
				'string' => $responseString,
				'status' => $this->statusCodeString,
				'headers' => $this->responseHeaders
			];
			throw new Exceptions\Custom("unable to send headers - already sent", null, null, $exceptionData);
			exit();
		}
	}
	
	/**
	 * returns the mapped description to a status code
	 * 
	 * @param integer $code
	 * @return string
	 */
	private function getCodeValue($code) {
		if (array_key_exists($code, $this->statusCodesMap)) {
			return $this->statusCodesMap[$code];
		}
		return "n/a";
	}
}