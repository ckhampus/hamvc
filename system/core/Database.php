<?php
require_once('Database/Model.php');
require_once('Database/Exception.php');

class Database extends Singleton {	
	private $username;
	private $password;
	private $hostname;
	private $database;
	private $driver;
	private $pdo;
	
	public static function connect($config) {
		if (!isset(self::$instance)) {
            self::$instance = new Database($config);
        }
        
        return static::$instance;
	}
	
	public static function getInstance() {
		if (!isset(self::$instance)) {
			throw new Database_Exception(new Exception('Not connected to database. Call \'Database::connect()\' first.'));
        }
		
        return self::$instance;
	}
	
	private function __construct($config) {
		$this->username = $config['username'];
		$this->password = $config['password'];
		$this->hostname = $config['hostname'];
		$this->database = $config['database'];
		$this->driver = $config['dbdriver'];
		
		try {
			$pdo = &$this->pdo;
			
			switch($this->driver) {
				case 'mysql':
					$conn_string = sprintf('mysql:host=%s;dbname=%s', $this->hostname, $this->database);
					break;
				case 'pgsql':
					$conn_string = sprintf('pgsql:host=%s;dbname=%s', $this->hostname, $this->database);
					break;
				case 'sqlite':
					$conn_string = sprintf('sqlite:%s', $this->database);
					break;
				default:
					$conn_string = false;
					break;
			}
			
			$pdo = new PDO($conn_string, $this->username, $this->password);
			unset($pdo);
		}
		catch (PDOException $e) {
			throw new Database_Exception($e);
		}
	}
	
	
	private function queryType($sql) {
		$sql = strtolower($sql);
	
		if(strstr($sql, ' ', TRUE) == 'select') {
			return 'SELECT';
		}
		else if(strstr($sql, ' ', TRUE) == 'update') {
			return 'UPDATE';
		}
		else if(strstr($sql, ' ', TRUE) == 'insert') {
			return 'INSERT';
		}
		else if(strstr($sql, ' ', TRUE) == 'delete') {
			return 'DELETE';
		}
		else {
			return NULL;
		}
	} 
	
	public function __call($method, $params) {

		if (substr($method,0,3) == 'get') {
			$class = substr($method, 3);
			$table = strtolower($class);
			$id = $params[0];
			
			$result = call_user_func(array($class, "findById"), $id);
		
			return $result;
		} 
		else if (substr($method,0,3) == 'set') {
			return NULL;
		} 
		else {
			return NULL;
		}
	} 
	
	public function query($sql, $values = NULL, $class = 'stdClass') {
		$pdo = &$this->pdo;
		$type = $this->queryType($sql);
		$result = NULL;

		try {
			// use prepared statements if value array is set.
			if(isset($values)) {
				if(!is_array($values)) {
					throw new InvalidArgumentException('\'$values\' is not of type Array.');
				}
				
				if(empty($values)) {
					throw new Database_Exception(new Exception('\'$values\' is empty.'));
				}
				
				$stm = $pdo->prepare($sql);
				
				// check if statement execution was successful.
				if(!$stm->execute($values)) {
					$err = $stm->errorInfo();
				
					throw new Database_Exception(new Exception($err[2], (int)$err[0]));
				}
			}
			else {
				$stm = $pdo->prepare($sql);
				
				// check if statement execution was successful.
				if(!$stm->execute()) {
					$err = $stm->errorInfo();
				
					throw new Database_Exception(new Exception($err[2], (int)$err[0]));
				}
			}
			
			// unset the PDO object reference
			unset($pdo);
			
			if($type === 'SELECT') {
				$stm->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $class);
				$result = $stm->fetchAll();
			}
			else if($type === 'UPDATE') {
				$result = TRUE;
			}
			else if($type === 'INSERT') {
				$result = TRUE;
			}
			else if($type === 'DELETE') {
				$result = TRUE;
			}
			
			if(is_array($result) && !empty($result)) {
				if(count($result) === 1) {
					return $result[0];
				}
				
				return $result;
			}
			
			return NULL;
		}
		catch (PDOException $e) {
			throw new Database_Exception($e);
		}
	}
}