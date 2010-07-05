<?php
class Request {
	public function load($controller, $action = NULL, $parameters = NULL) {
		if(empty($controller)) {
			$controller = DEFAULT_CONTROLLER;
		}
	
		include_once(APP_PATH.'/controllers/'.$controller.'.php');
		
		$c = new $controller;

		if(isset($action) && !empty($action)) {
			if(isset($parameters) && !empty($parameters)) {
				call_user_func_array(array($c, $action), $parameters);
			}
			else {
				call_user_func(array($c, $action));
			}
		}
		else {
			$c->index();
		}
	}
}