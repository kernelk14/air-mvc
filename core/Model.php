<?php

abstract class Model {
    
    protected $tableName;
    private $conn;
    private $result;
    
    public function __construct() {
        require_once CONFIG_DIR . 'Database.php';
        try {
            $this->conn = new PDO("mysql:host=$creds['host']; dbname=$creds['db']; charset=utf8", $creds['user'], $creds['pass']);
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<script>console.log('Database (PDO) connected successfully')</script>";
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
    
    public function insert(array $keyValue) {
        $fields = '`' . implode('`, `', array_keys($keyValue)) . '`';
        $values = ':' . implode(', :', array_keys($keyValue));
        $query = "INSERT INTO {$this->tableName} ($fields) VALUES ($values)";
        $this->result = $this->conn->prepare($query);
        $this->result->execute($keyValue);
    }
    
    public function getAll(): array {
        try {
            $query = "SELECT * FROM {$this->tableName}";
            $this->result = $this->conn->prepare($query);
            $this->result->execute();
            
            return $this->result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Cannot fetch all: " . $e->getMessage();
        }
    }
    
    public function find(array $data) {
        $spec_key = array_keys($data);
        $spec_val = $data[$spec_key];
        try {
            $query = "SELECT * FROM {$this->tableName} WHERE {$spec_key} = ?";
            $this->result = $this->conn->prepare($query);
            $this->result->execute([$spec_val]);
            
            return $this->result->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Cannot fetch contents of $spec_key: " . $e->getMessage();
        }
    }
    
}