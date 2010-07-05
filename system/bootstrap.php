<?php
error_reporting(E_ALL);

Autoloader::register();

include_once(SYSTEM_PATH.'/helpers/html.php');

try {
	Dispatcher::dispatch();
}
catch(ClassNotFoundException $e) {
	echo $e->getMessage();
	exit($e->getCode());
}
catch(Exception $e) {
	echo $e->getMessage();
	exit($e->getCode());
}

/*
 * Autoloader class
 */
class Autoloader {
	public static function register() {
		spl_autoload_register(NULL, FALSE);
		spl_autoload_extensions('.php');
		spl_autoload_register(array('Autoloader', 'loadCoreFiles'));
		spl_autoload_register(array('Autoloader', 'loadClassFiles'));
	}

	private static function loadClasses($class, $file) {
		if(class_exists($class, FALSE)) {
			return;
		}	
	
		if(file_exists($file)) {
			require_once($file);
			
			if(!class_exists($class, FALSE)) {
				throw new ClassNotFoundException('Class '.$class.' not found');
			}
		}
	}
	
	private static function loadCoreFiles($class) {
		$file = SYSTEM_PATH.'/core/'.str_replace('_', '/', $class).'.php';
		
		static::loadClasses($class, $file);
	}	
	
	private static function loadClassFiles($class) {
		$file = SYSTEM_PATH.'/classes/'.$class.'.php';
		
		static::loadClasses($class, $file);
	}
}

/*
 * Custom exceptions
 */
class ClassNotFoundEsxception extends Exception {}