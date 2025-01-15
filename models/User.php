<?php
class User {
    private $conn;
    private $table = "users";
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $balance;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET username=:username, email=:email, 
                     password_hash=:password_hash";
        
        $stmt = $this->conn->prepare($query);
        
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password);
        
        return $stmt->execute();
    }
    
    public function transfer($receiver_id, $amount) {
        $this->conn->beginTransaction();
        try {
            // Deduct from sender
            $query = "UPDATE " . $this->table . " 
                     SET balance = balance - :amount 
                     WHERE id = :sender_id AND balance >= :amount";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":amount", $amount);
            $stmt->bindParam(":sender_id", $this->id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                throw new Exception("Insufficient funds");
            }
            
            // Add to receiver
            $query = "UPDATE " . $this->table . " 
                     SET balance = balance + :amount 
                     WHERE id = :receiver_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":amount", $amount);
            $stmt->bindParam(":receiver_id", $receiver_id);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
