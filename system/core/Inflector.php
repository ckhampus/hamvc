<?php
class Inflector extends Singleton {
	private static $instance = NULL;
	private static $exceptions;
	
	private function __construct() {}

	public static function exception($singular, $plural = NULL) {
		if (!isset(static::$instance)) {
				self::getInstance();
		}
	
		if($plural === NULL) {
			self::$exceptions[$singular] = $singular;
		}
		
		self::$exceptions[$singular] = $plural;
	}
	
	public static function pluralize($word) {
		if (!isset(static::$instance)) {
				self::getInstance();
		}
	
		$vowels = array('a','e','i','o','u','y');
		
		if(isset(self::$exceptions[$word]) && !empty(self::$exceptions[$word])) {
			return self::$exceptions[$word];
		}
		
		foreach(array('s','z','x','sh','ch') as $e) {
			if(strrchr($word, $e) === $e) {
				return $word . 'es';
			}
		}
		
		if(substr($word, -1) === 'y') {
			
			foreach($vowels as $vowel) {
				if(substr($word, -2, 1) === $vowel) {
					return $word . 's';
				}
			}
			
			return substr($word, 0, -1) . 'ies';
		}
		
		if(substr($word, -1) === 'o') {
			
			foreach($vowels as $vowel) {
				if(substr($word, -2, 1) === $vowel) {
					return $word . 's';
				}
			}
			
			return $word . 'es';
		}
		
		if(substr($word, -1) === 'f') {
			return substr($word, 0, -1) . 'ves';
		}
		
		if(substr($word, -2) === 'fe') {
			return substr($word, 0, -2) . 'ves';
		}
		
		return $word . 's';
	}
}