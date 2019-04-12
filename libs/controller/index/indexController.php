<?php 

namespace ATFApp\Controller\Index;

use ATFApp\BasicFunctions AS BasicFunctions;
use ATFApp\ProjectConstants AS ProjectConstants;
use ATFApp\Exceptions as Exceptions;

use ATFApp\Helper as Helper;
use ATFApp\Core as Core;


/**
 * index controller
 * 
 * must extend BaseController
 * (or implement all its public methods)
 *
 */
class IndexController extends \ATFApp\Controller\BaseController {
	
	/**
	 * index action 
	 * @return multitype:array|string
	 */
	public function indexAction() {
		# return an array 
		return ["name" => "ATF-Php"];
	}
	
}