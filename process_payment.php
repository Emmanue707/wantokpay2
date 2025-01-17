<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure no output before sending the response
ob_start();

require_once 'Database.php';
require_once 'User.php';
require_once 'vendor/autoload.php';

// Set Stripe secret key
\Stripe\Stripe::setApiKey('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    try {
        header('Content-Type: application/json');
        
        // Check if qr_data is being sent and is valid
        if (!isset($_POST['qr_data'])) {
            throw new Exception("No QR data received");
        }

        $qrData = json_decode($_POST['qr_data'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid QR data JSON format");
        }

        // Initialize database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Get customer payment info
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (empty($user['stripe_customer_id'])) {
            throw new Exception("User has no Stripe customer ID");
        }

        // Get customer's default payment method
        $paymentMethods = \Stripe\PaymentMethod::all([
            'customer' => $user['stripe_customer_id'],
            'type' => 'card',
        ]);

        if (empty($paymentMethods->data)) {
            throw new Exception("No payment methods found for the user");
        }

        // Use the first payment method as the default one
        $defaultPaymentMethod = $paymentMethods->data[0]->id;

        // Create payment intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $qrData['amount'] * 100,  // Amount in cents
            'currency' => 'pgk',  // Papua New Guinean Kina (ensure this currency is valid in Stripe)
            'customer' => $user['stripe_customer_id'],
            'payment_method' => $defaultPaymentMethod,
            'payment_method_types' => ['card'],
            'metadata' => [
                'qr_payment' => true
            ]
        ]);

        // Prepare response
        $response = [
            'success' => true,
            'payment_intent' => $paymentIntent->client_secret,
            'payment_method' => $defaultPaymentMethod
        ];

        // Send success response
        echo json_encode($response);
        exit;

    } catch (Exception $e) {
        // In case of error, send failure response
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
