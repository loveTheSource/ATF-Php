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
		$validator = new Helper\Validator();
		
		$data = array('value1' => '', 'value2' => "wef", 'value3' => '');
		$methods = array('value1' => array('required', 'minlen:3'), 'value2' => array('int'), 'value3'=>array());
		
		$res = $validator->validate($data, $methods);
		if ($res) {
			var_dump("success");
		} else {
			#var_dump($validator->getErrors());
		}

		return array("msg" => "Welcome");
	}
	
}