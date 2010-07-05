<?php
abstract class Database_Model {
	private $columns = array('id' => '');
	private $required;
	private $actions;
	private $db;
	
	public function __construct($id = NULL) {
		$this->db = Database::getInstance();
	
		if(is_numeric($id) && isset($id)) {
			$temp = $this->findById($id);
			
			if(isset($temp)) {
				foreach($temp->columns as $key => $value) {
					$this->columns[$key] = $value;
				}
			}
		}
	}
	
	protected function hasAction($name, $callback) {
		$this->actions[$name] = $callback;
	}
	
	protected function hasColumn($name, $required = FALSE) {
		$this->columns[$name] = '';
		
		if(is_bool($required)) {
			$this->required[$name] = $required;
		}
	}
	
	public function delete() {
		$db = Database::getInstance();
		$class = get_class($this);
		$table = Inflector::pluralize(strtolower($class));
		
		$sql = 'DELETE FROM ' . $table . ' WHERE ';
		foreach ($this->columns as $field => $value) {
			$sql .= $field . '=:' . $field . ' AND ';
		}
		$sql = substr($sql, 0, -5);
			
		$db->query($sql, $this->columns);
	}

	public function save() {
		$db = Database::getInstance();
		$class = get_class($this);
		$table = Inflector::pluralize(strtolower($class));

		if(!isset($this->id) || empty($this->id)) {
			foreach ($this->required as $field => $value) {
				if($value === TRUE && empty($this->$field)) {
					throw new DatabaseException(new Exception("The field '{$field}' is required and therefor not be empty. <br />"));
				}				
			}
			/*
			$sql = 'INSERT INTO ' . $table;
			$sql .= ' ('.implode(', ', array_keys($this->columns)).') VALUES';
			$sql .= '(:'.implode(', :', array_keys($this->columns)).')';
			*/
			
			$sql = 'INSERT INTO ' . $table;
			foreach ($this->columns as $field => $value) {
				$sql .= $field . '=:' . $field . ', ';
			}
			$sql = substr($sql, 0, -2);
			
			$db->query($sql, $this->columns);
		}
		else {
			foreach ($this->required as $field => $value) {
				if($value === TRUE && empty($this->$field)) {
					throw new DatabaseException(new Exception("The field '{$field}' is required and can therefor not be empty. <br />"));
				}				
			}
			
			$sql = 'UPDATE ' . $table . ' SET ';
			foreach ($this->columns as $field => $value) {
				$sql .= $field . '=:' . $field . ', ';
			}
			$sql = substr($sql, 0, -2);
			$sql .= ' WHERE id=:id';
			
			$db->query($sql, $this->columns);
		}
	}
	
	public static function findAll() {
		$class = get_called_class();
		$table = Inflector::pluralize(strtolower($class));
		$result = $db->query("SELECT * FROM {$table}", NULL, $class);
	}
	
	public static function __callStatic($method, $params) {
		$db = Database::getInstance();

		if (substr($method,0,6) == 'findBy') {
			$class = get_called_class();
			$field = substr($method, 6);
			$table = Inflector::pluralize(strtolower($class));
			
			if(count($params) === 1) {
				$args = $params[0];
			}
			else {
				$args = $params;
			}
			
			if(is_array($args)) {
				foreach ($args as $v) {
					$result[] = $db->query("SELECT * FROM {$table} WHERE {$field}=:{$field}", array("{$field}" => $v), $class);
				}
			}
			else if (isset($args)) {
				$result = $db->query("SELECT * FROM {$table} WHERE {$field}=:{$field}", array("{$field}" => $args), $class);
			}
			else {
				$result = NULL;
			}
		
			return $result;
		} 
		else if (substr($method,0,3) == 'set') {
			return NULL;
		} 
		else {
			return NULL;
		}
	}
	
	public function __get($name) {
        if (isset($this->columns[$name])) {
            return $this->columns[$name];
        } else {
            return null;
        }
    }

    public function __isset($name) {
        return isset($this->columns[$name]);
    }

    public function __set($name, $value) {
		if(isset($this->actions[$name])) {
			$callback = $this->actions[$name];
		
			if(method_exists($this, $callback)) {
				$value = call_user_func(array($this, $callback), $value);
			}
			else if (function_exists($callback)) {
				$value = call_user_func($callback, $value);
			}
		}
	
        $this->columns[$name] = $value;
    }
}