<?php
session_start();
require_once 'Database.php';

$db = new Database()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $requestType = $_POST['requestType'];
    $merchant_id = $_SESSION['user_id'];
    
    // Generate unique link token
    $link_token = bin2hex(random_bytes(16));
    
    if ($requestType === 'specific') {
        $username = $_POST['username'];
        
        $stmt = $db->prepare("INSERT INTO payment_links (merchant_id, amount, description, recipient_username, link_token, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$merchant_id, $amount, $description, $username, $link_token]);
        
        echo json_encode([
            'success' => true,
            'requestType' => 'specific',
            'message' => 'Request sent successfully',
            'link_token' => $link_token
        ]);
    } else {
        $stmt = $db->prepare("INSERT INTO payment_links (merchant_id, amount, description, link_token, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$merchant_id, $amount, $description, $link_token]);
        
        $paymentLink = "send_money.php?token=" . $link_token;
        
        echo json_encode([
            'success' => true,
            'requestType' => 'general',
            'paymentLink' => $paymentLink,
            'link_token' => $link_token
        ]);
    }
}
