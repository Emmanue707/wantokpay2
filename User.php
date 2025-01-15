<?php
class User {
    private $conn;
    private $table = "users";
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $stripe_customer_id;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET username=:username, email=:email, 
                     password_hash=:password_hash";
        
        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $password_hash);
        
        return $stmt->execute();
    }

    public function processPayment($receiver_id, $amount) {
        try {
            // Get sender's Stripe customer ID
            $stmt = $this->conn->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
            $stmt->execute([$this->id]);
            $sender = $stmt->fetch(PDO::FETCH_ASSOC);

            // Process payment through Stripe
            $payment = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'pgk',
                'customer' => $sender['stripe_customer_id'],
                'transfer_data' => [
                    'destination' => $receiver_id,
                ],
            ]);

            if ($payment->status === 'succeeded') {
                // Record transaction
                $stmt = $this->conn->prepare("INSERT INTO transactions 
                    (sender_id, receiver_id, amount, type, status) 
                    VALUES (?, ?, ?, 'card_payment', 'completed')");
                $stmt->execute([$this->id, $receiver_id, $amount]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
