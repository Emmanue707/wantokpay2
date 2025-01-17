<?php
session_start();
require_once 'Database.php';
require_once 'User.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $qr_data = json_decode($_POST['qr_data'], true);
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Get customer's Stripe ID
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate base amount and fee
        $baseAmount = $qr_data['amount'];
        $fee = ($baseAmount >= 100) ? $baseAmount * 0.05 : 0;
        $totalAmount = ($baseAmount + $fee) * 100; // Convert to cents for Stripe

        // Create the main payment intent with the total amount
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $totalAmount,
            'currency' => 'pgk',
            'customer' => $user['stripe_customer_id'],
            'payment_method' => $defaultPaymentMethod,
            'payment_method_types' => ['card'],
            'application_fee_amount' => $fee * 100, // Fee in cents
            'transfer_data' => [
                'destination' => 'WANTOK_PAY_CONNECTED_ACCOUNT_ID',
            ],
            'metadata' => [
                'qr_payment' => true,
                'base_amount' => $baseAmount,
                'fee_amount' => $fee
            ]
        ]);

        // Record transaction
        $stmt = $db->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, type, status) 
                            VALUES (?, ?, ?, 'qr_payment', 'completed')");
        $stmt->execute([$_SESSION['user_id'], $qr_data['merchant_id'], $qr_data['amount']]);

        echo json_encode(['success' => true, 'payment_intent' => $payment_intent->client_secret]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
