<?php 

namespace ATFApp\Modules\ModuleIndex;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;


/**
 * cmd index 
 * 
 * must extend BaseCmds
 * (or implement all its public methods)
 *
 */
class CmdIndex extends \ATFApp\Modules\BaseCmds {
	
	/**
	 * index action 
	 * @return multitype:array|string
	 */
	public function indexAction() {
		# return an array 
		return array("name" => "ATF-Php");
	}
	
}