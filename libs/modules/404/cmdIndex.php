<?php

namespace ATFApp\Modules\Module404;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * index cmd
 * 
 * must extend BaseCmds
 * (or implement all its public methods)
 *
 */
class CmdIndex extends \ATFApp\Modules\BaseCmds {
	
	/**
	 * index action (404)
	 * 
	 * @return multitype:array|string
	 */
	public function indexAction() {
		// consider logging 404 requests
		
		return array("msg" => BasicFunctions::getLangText('basics', 'message_404'));
		
		// forward to home instead of returning
		// $this->forwardToModule('index');
	}
	
}