<?php
session_start();
require_once 'Database.php';

$link_token = $_POST['token'];
$db = new Database()->getConnection();

$stmt = $db->prepare("SELECT * FROM payment_links WHERE link_token = ? AND status = 'active'");
$stmt->execute([$link_token]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if($payment) {
    // Process payment using existing payment logic
    // Update payment_links status to 'used'
    $stmt = $db->prepare("UPDATE payment_links SET status = 'used' WHERE id = ?");
    $stmt->execute([$payment['id']]);
    
    echo json_encode(['success' => true]);
}
