<?php
require_once 'vendor/autoload.php';
use Endroid\QrCode\QrCode;

class QRCodeGenerator {
    private $conn;
    private $table = "qr_codes";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function generatePaymentQR($merchant_id, $amount, $description) {
        $qr_data = [
            'merchant_id' => $merchant_id,
            'amount' => $amount,
            'timestamp' => time()
        ];
        
        $qr = new QrCode(json_encode($qr_data));
        
        $query = "INSERT INTO " . $this->table . " 
                 SET merchant_id=:merchant_id, amount=:amount, 
                     description=:description, status='active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":merchant_id", $merchant_id);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":description", $description);
        
        if($stmt->execute()) {
            return $qr->writeString();
        }
        return false;
    }
}
