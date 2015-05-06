<?php

namespace ATFApp\Modules\ModuleTest;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;


/**
 * cmd restricted 
 * 
 * must extend BaseCmds
 * (or implement all its public methods)
 *
 */
class CmdRestricted extends \ATFApp\Modules\BaseCmds {
	
	/**
	 * index action 
	 * 
	 * @return multitype:array|string
	 */
	public function indexAction() {
		// forward to login action
		return array('msg' => "Access granted to restricted cmd...");
	}
}