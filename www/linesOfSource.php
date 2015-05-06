<?php


class linesCounterClass {
	var $files_counter = 0;
	var $lines_counter = 0;
	var $ext2count = array("php", "xsl", "css", "lng", "phtml", "js");
	var $excludeFolders = array(".svn", "tiny_mce", ".settings", "temp", "jquery");
	
	function parseDir($dir, $depth) {
		$dirContent = scandir($dir);
		
		foreach ($dirContent AS $item) {
			if (is_dir($dir.'/'.$item) && $item!=".." && $item!="." && !in_array($item, $this->excludeFolders)) {
				echo '<br/>== '.$item.' ==';
				$depth++;
				$this->parseDir($dir.$item."/", $depth);
			} elseif (is_file($dir.'/'.$item)) {
				$thisFile = basename(__FILE__);
				if ($item != $thisFile) {
					$extArr = explode(".", $item);
					$ext = $extArr[count($extArr)-1];
					
					if (in_array($ext, $this->ext2count)) {
						$this->files_counter++;
						$fileContent = file($dir.'/'.$item);
						$this->lines_counter += count($fileContent);
						echo '<br/>file: '.$item.' - '.count($fileContent);
					}
				}
			}
		}
	}
	
	
}

$count = new linesCounterClass();
$count->parseDir("../", 0);


echo '<h2>'.$count->files_counter.' Dateien</h2> ('.implode(", ", $count->ext2count).')';
echo '<h2>'.$count->lines_counter.' Zeilen Code</h2>';

?>
