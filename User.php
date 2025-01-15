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
                     password_hash=:password_hash, balance=0.00";
        
        $stmt = $this->conn->prepare($query);
        
        $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $password_hash);
        
        return $stmt->execute();
    }
    
         public function transfer($receiver_id, $amount) {
             try {
                 // Create Stripe transfer
                 $transfer = \Stripe\Transfer::create([
                     'amount' => $amount * 100, // Convert to cents
                     'currency' => 'pgk',
                     'destination' => $receiver_id,
                     'transfer_group' => 'WANTOKPAY_' . time()
                 ]);

                 // If Stripe transfer successful, update local database
                 if ($transfer->status === 'succeeded') {
                     return parent::transfer($receiver_id, $amount);
                 }
             } catch (\Stripe\Exception\ApiErrorException $e) {
                 return false;
             }
         }
}
