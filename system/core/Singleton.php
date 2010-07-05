<?php
abstract class Singleton {
	private static $instance = NULL;
	
	private function __construct() {}
	
	public static function getInstance() {
		if(self::$instance === NULL) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	final private function __clone() {}
}