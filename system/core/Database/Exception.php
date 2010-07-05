<?php
class Database_Exception extends PDOException {
	public function __construct($e) {
		parent::__construct();
		
		$this->code = $e->getCode(); 
        $this->message = $e->getMessage(); 
		
		if(strstr($e->getMessage(), 'SQLSTATE[')) { 
            preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches); 
            $this->code = ($matches[1] == 'HT000' ? $matches[2] : $matches[1]); 
            $this->message = $matches[3]; 
        } 
	}
}