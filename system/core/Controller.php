<?php

abstract class Controller {
	private $config;
	
	private $view;
	private $view_data = array();
	
	function __construct() {
		$this->config = &$GLOBALS['config'];
	}
	
	abstract function index();

	protected function loadView($view_name) {
		$this->view = $view_name;
	}

	protected function setViewData($key, $value) {
		$this->view_data[$key] = $value;
	}
	
	protected function display() {
		extract($this->view_data);
		include(APP_PATH.'/views/'.$this->view.'.php');
	}
	
	public function __get($name) {
        if (isset($this->view_data[$name])) {
            return $this->view_data[$name];
        } else {
            return null;
        }
    }

    public function __isset($name) {
        return isset($this->view_data[$name]);
    }

    public function __set($name, $value) {
        $this->view_data[$name] = $value;
    }
}