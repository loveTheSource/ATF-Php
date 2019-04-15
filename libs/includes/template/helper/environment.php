<?php

namespace ATFApp\Template\Helper;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

class TemplateHelperEnvironment {
	
	public function getHelper() {
		if (ProjectConstants::ENVIRONMENT_BADGE === true) {
			$template = Core\Factory::getTemplateObj();
			return $template->renderHelper('environment');	
		}
	}
	
}