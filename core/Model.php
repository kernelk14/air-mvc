<?php

abstract class Model {
    
    protected $tableName;
    private $conn;
    private $result;
    
    public function __construct() {
        require_once CONFIG_DIR . 'Database.php';
        try {
            $this->conn = new PDO("mysql:host={$creds['host']}; dbname={$creds['db']}; charset=utf8", $creds['user'], $creds['pass']);
            
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
            throw new Exception("Cannot fetch all: " . $e->getMessage());
        }
    }
    
    public function find(array $data) {
        $spec_key = array_keys($data);
        $spec_val = $data[$spec_key[0]];
        try {
            $query = "SELECT * FROM {$this->tableName} WHERE {$spec_key} = ?";
            $this->result = $this->conn->prepare($query);
            $this->result->execute([$spec_val]);
            
            return $this->result->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Cannot fetch contents of $spec_key: " . $e->getMessage());
        }
    }
    
    public function update(array $data, array $conditions) {
        try {
            $updateFields = '';
            foreach (array_keys($data) as $field) {
                $updateFields .= "$field = :$field, ";
            }
            $updateFields = rtrim($updateFields, ', ');
            
            $whereClause = '';
            foreach (array_keys($conditions) as $field) {
                $whereClause .= "$field = :where_$field AND ";
            }
            $whereClause = rtrim($whereClause, ' AND ');
            
            $query = "UPDATE {$this->tableName} SET $updateFields WHERE $whereClause";
            $this->result = $this->conn->prepare($query);
            
            $params = array_merge($data, $conditions);
            foreach ($conditions as $key => $value) {
                $params['where_' . $key] = $value;
            }
            
            $this->result->execute($params);
            
            return $this->result->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Cannot update: " . $e->getMessage());
        }
    }
    
    public function delete(array $conditions) {
        try {
            $whereClause = '';
            foreach (array_keys($conditions) as $field) {
                $whereClause .= "$field = :$field AND ";
            }
            $whereClause = rtrim($whereClause, ' AND ');
            
            $query = "DELETE FROM {$this->tableName} WHERE $whereClause";
            $this->result = $this->conn->prepare($query);
            $this->result->execute($conditions);
            
            return $this->result->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Cannot delete: " . $e->getMessage());
        }
    }
    
}