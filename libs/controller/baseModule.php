<?php

namespace ATFApp\Controller;

use ATFApp\BasicFunctions as BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

/**
 * abstract base controller
 *
 */
abstract class BaseModule {
	

    public function getModuleData() {
        return [];
    }

    /**
	 * check access to module
	 * 
	 * @return boolean
	 */
	public function canAccess() {
		return true;
	}

}