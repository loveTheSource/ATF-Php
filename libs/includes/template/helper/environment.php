<?php

namespace ATFApp\Template\Helper;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

class TemplateHelperEnvironment {
	
	public function getHelper() {
		$template = Core\Factory::getTemplateObj();
		return $template->renderHelper('environment');
	}
	
}