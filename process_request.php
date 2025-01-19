<?php
session_start();
require_once 'Database.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('your_stripe_secret_key');

$db = new Database();
$db = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $requestType = $_POST['requestType'];
    $merchant_id = $_SESSION['user_id'];
    
    // Create Stripe Payment Intent
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $amount * 100, // Convert to cents
        'currency' => 'pgk',
        'description' => $description,
        'metadata' => [
            'merchant_id' => $merchant_id
        ]
    ]);
    
    // Generate unique link token
    $link_token = $payment_intent->client_secret;
    
    if ($requestType === 'specific') {
        $username = $_POST['username'];
        $stmt = $db->prepare("INSERT INTO payment_links (merchant_id, amount, description, recipient_username, link_token, stripe_payment_intent, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$merchant_id, $amount, $description, $username, $link_token, $payment_intent->id]);
    } else {
        $stmt = $db->prepare("INSERT INTO payment_links (merchant_id, amount, description, link_token, stripe_payment_intent, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$merchant_id, $amount, $description, $link_token, $payment_intent->id]);
    }
    
    $paymentLink = "send_money.php?token=" . $link_token;
    
    echo json_encode([
        'success' => true,
        'requestType' => $requestType,
        'paymentLink' => $paymentLink,
        'clientSecret' => $payment_intent->client_secret
    ]);
}
