<?php
class Dispatcher extends Singleton {
	public static function dispatch() {
		$route = &$GLOBALS['route'];
		
		$parsed_url = parse_url(substr($_SERVER['REQUEST_URI'], strlen(BASE_PATH)));
		$controller = NULL;
		$action = NULL;
		$parameters = NULL;

		// Check if path is specified.
		if(isset($parsed_url['path'])) {
			$path = $parsed_url['path'];
			
			foreach($route as $key => $val) {
				// Convert wild-cards to RegEx
				$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
				
				// Does the RegEx match?
				if (preg_match('#^'.$key.'$#', $path))
				{			
					// Do we have a back-reference?
					if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
					{
						$val = preg_replace('#^'.$key.'$#', $val, $path);
					}
					
					$path = $val;
					break;
				}
			}
			
			$segments = explode('/', $path);
		
			foreach($segments as $s) {				
				if(isset($controller)) {
					if(!is_dir(APP_PATH.'/controllers/'.$controller.'/'.$segments[0])) {
						break;
					}
					
					$controller .= '/'.$segments[0];
				}
				else {
					if(!is_dir(APP_PATH.'/controllers/'.$segments[0])) {
						break;
					}
					
					$controller = $segments[0];
				}
				
				array_shift($segments);
			}

			if(isset($controller) AND !empty($controller)) {
				$controller .= '/'.$segments[0];
			}
			else if(!empty($segments[0])) {					
				$controller = $segments[0];
			}

			if(count($segments) > 1) {
				$action = $segments[1];
				
				if(count($segments) > 2) {
					$parameters = array_slice($segments, 2);
				}
			}
		}

		// Check if query exists.
		if(isset($parsed_url['query'])) {
			$temp = array();
			parse_str($parsed_url['query'], $temp);
			
			$query = $temp;
		}

		Request::load($controller, $action, $parameters);
	}
}