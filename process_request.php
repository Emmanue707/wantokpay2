<?php
session_start();
require_once 'Database.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');

$db = new Database();
$db = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $requestType = $_POST['requestType'];
    $merchant_id = $_SESSION['user_id'];
    
    // Create Stripe Payment Intent
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $amount * 100,
        'currency' => 'pgk',
        'description' => $description,
        'metadata' => [
            'merchant_id' => $merchant_id
        ]
    ]);
    if ($requestType === 'specific') {
        $username = $_POST['username'];
        
        // Get recipient's user_id
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($recipient) {
            // Create payment link
            $stmt = $db->prepare("INSERT INTO payment_links (merchant_id, amount, description, recipient_username, link_token, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$merchant_id, $amount, $description, $username, $payment_intent->client_secret]);
            
            // Create notification
            $message = "New payment request of K{$amount} from {$_SESSION['username']}";
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, link_token, amount) VALUES (?, 'payment_request', ?, ?, ?)");
            $stmt->execute([$recipient['id'], $message, $payment_intent->client_secret, $amount]);
        }
        
        echo json_encode([
            'success' => true,
            'requestType' => 'specific',
            'message' => 'Payment request sent successfully'
        ]);
    } else {
        // Handle general payment link
        $stmt = $db->prepare("INSERT INTO payment_links (merchant_id, amount, description, link_token, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$merchant_id, $amount, $description, $payment_intent->client_secret]);
        
        echo json_encode([
            'success' => true,
            'requestType' => 'general',
            'clientSecret' => $payment_intent->client_secret,
            'paymentLink' => "send_money.php?token=" . $payment_intent->client_secret
        ]);
    }
}