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
        
        // Get the customer's payment method
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Create payment intent with the customer's payment method
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => 'pgk',
            'customer' => $user['stripe_customer_id'],
            'payment_method_types' => ['card'],
            'metadata' => [
                'qr_payment' => true,
                'transaction_id' => $transaction_id
            ]
        ]);

        header('Content-Type: application/json');

        $response = [
            'success' => true,
            'payment_intent' => $paymentIntent->client_secret,
            'customer' => $user['stripe_customer_id']
        ];

        echo json_encode($response);
        exit;
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
