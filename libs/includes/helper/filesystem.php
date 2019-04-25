<?php

namespace ATFApp\Helper;

use ATFApp\Exceptions;

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
	

	public function scanFolder($folder, $removeDots=true) {
		try {
			if (!is_dir($folder)) {
				throw new Exceptions\Custom("folder not found: " . $folder);
			} else {
				$files = scandir($folder);
				if (is_array($files)) {
					if ($removeDots) {
						$files = array_diff($files, ['..', '.']);
					}
					return $files;
				} else {
					throw new Exceptions\Custom("failed to scan folder: " . $folder);
				}
			}
		} catch (\Throwable $e) {
			throw $e;
		}
	}
}