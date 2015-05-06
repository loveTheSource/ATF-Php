<?php

namespace ATFApp\Helper;

class Filesystem {
	
	public function __construct() { }
	
	/**
	 * delete a folder and its contents recursively
	 *
	 * @param string $dir
	 * @return boolean
	 */
	public function delTree($dir) {
		if (substr($dir, -1) != DIRECTORY_SEPARATOR) $dir .= DIRECTORY_SEPARATOR;
	
		$files = scandir($dir);
		foreach ($files as $file) {
			if (!in_array($file, array('.','..'))) {
				$tmpPath = $dir . $file;
				if (is_dir($tmpPath)) {
					// call recursively
					$this->delTree($tmpPath);
				} else {
					unlink($tmpPath);
				}
			}
		}
		return rmdir($dir);
	}
	
	/**
	 * get the file mime type
	 * 
	 * @param string $file
	 * @return type (as string)
	 */
	public function getMimeType($file) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
		$res = finfo_file($finfo, $file);
		finfo_close($finfo);
		return $res;
	}
}