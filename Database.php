<?php
class Database {
    private $host = "localhost";
    private $db_name = "u787474055_wantokpay2";  // Your existing database name
    private $username = "u787474055_panther707"; // Your existing username
    private $password = "Blackpanther707@";      // Your existing password
    public $conn;
    
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
