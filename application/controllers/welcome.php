<?php
class Welcome extends Controller {	
	public function index() {		
		$this->loadView('base');
		
		$this->name = 'Cristian';
		
		$this->display();
	}
}