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
        $database = new Database();
        $db = $database->getConnection();
    
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Get customer's default payment method
        $paymentMethods = $stripe->paymentMethods->all([
            'customer' => $user['stripe_customer_id'],
            'type' => 'card',
        ]);
        $defaultPaymentMethod = $paymentMethods->data[0]->id;
    
        // Calculate amounts
        $baseAmount = $qrData['amount'];
        $fee = ($baseAmount >= 100) ? $baseAmount * 0.05 : 0;
        $totalAmount = ($baseAmount + $fee) * 100;
    
        // Create payment intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $totalAmount,
            'currency' => 'pgk',
            'customer' => $user['stripe_customer_id'],
            'payment_method' => $defaultPaymentMethod,
            'payment_method_types' => ['card'],
            'metadata' => [
                'qr_payment' => true,
                'base_amount' => $baseAmount,
                'fee_amount' => $fee
            ]
        ]);
    
        $response = [
            'success' => true,
            'payment_intent' => $paymentIntent->client_secret,
            'payment_method' => $defaultPaymentMethod
        ];
    
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
