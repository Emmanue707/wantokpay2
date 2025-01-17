<?php
session_start();

// Ensure no output before sending the response
ob_start();

require_once 'Database.php';
require_once 'User.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    try {
        header('Content-Type: application/json');
        
        $qrData = json_decode($_POST['qr_data'], true);
        
        $database = new Database();
        $db = $database->getConnection();
        
        // Get customer payment info
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get customer's default payment method
        $paymentMethods = $stripe->paymentMethods->all([
            'customer' => $user['stripe_customer_id'],
            'type' => 'card',
        ]);
        $defaultPaymentMethod = $paymentMethods->data[0]->id;

        // Create payment intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $qrData['amount'] * 100,
            'currency' => 'pgk',
            'customer' => $user['stripe_customer_id'],
            'payment_method' => $defaultPaymentMethod,
            'payment_method_types' => ['card'],
            'metadata' => [
                'qr_payment' => true
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
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
} else {
    // Handle case where request method is not POST or user is not logged in
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// End of the script, clean any output buffering if used
ob_end_clean();
?>
