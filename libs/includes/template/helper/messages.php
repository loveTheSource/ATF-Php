<?php

namespace ATFApp\Template\Helper;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;

class TemplateHelperMessages {
	
	public function getHelper() {
		$messages = BasicFunctions::getMessages();
		if (!empty($messages)) {
			$template = Core\Factory::getTemplateObj();
			$template->setData('messages', $messages);
			return $template->renderHelper('messages');
		}
		return "";
	}
	
}