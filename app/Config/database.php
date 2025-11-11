<?php
class Database {
    
    private $host = 'localhost';
    private $db_name = 'linkinphp';
    private $username = 'phpuser';
    private $password = 'secret';
    private $charset = 'utf8mb4';
    private $conn = null;
    
    public function getConnection() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            return null;
        }
        return $this->conn;
    }
}
?>