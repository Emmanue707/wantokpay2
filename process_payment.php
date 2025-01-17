<?php
session_start();
require_once 'Database.php';
require_once 'User.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $qr_data = json_decode($_POST['qr_data'], true);
    
    try {
        header('Content-Type: application/json');
        
        // Log incoming QR data
        error_log("Received QR data: " . $_POST['qr_data']);
        
        $qrData = json_decode($_POST['qr_data'], true);
        
        // Get customer payment info
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log customer data
        error_log("Customer data: " . json_encode($user));
        
        // Create payment intent
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $qrData['amount'] * 100,
            'currency' => 'pgk',
            'customer' => $user['stripe_customer_id'],
            'payment_method_types' => ['card'],
            'metadata' => [
                'qr_payment' => true
            ]
        ]);
        
        // Log payment intent
        error_log("Payment Intent created: " . json_encode($paymentIntent));
        
        $response = [
            'success' => true,
            'payment_intent' => $paymentIntent->client_secret,
            'customer' => $user['stripe_customer_id']
        ];
        
        error_log("Sending response: " . json_encode($response));
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        error_log("Payment processing error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }}
