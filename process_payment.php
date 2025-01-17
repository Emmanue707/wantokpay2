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
        // Ensure the correct content type is set
        header('Content-Type: application/json');

        // Decode the incoming QR data
        $qrData = json_decode($_POST['qr_data'], true);

        // Log the QR data for debugging
        error_log("Received QR data: " . $_POST['qr_data']);
        
        // Set up the database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Retrieve the user's Stripe customer ID from the database
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Log the user data for debugging
        error_log("Customer data: " . json_encode($user));

        if (empty($user['stripe_customer_id'])) {
            throw new Exception("User does not have a Stripe customer ID.");
        }

        // Get payment methods associated with the user's Stripe account
        $paymentMethods = \Stripe\PaymentMethod::all([
            'customer' => $user['stripe_customer_id'],
            'type' => 'card',
        ]);

        // If no payment method exists, throw an error
        if (empty($paymentMethods->data)) {
            throw new Exception("No payment methods found for the user.");
        }

        // Use the first payment method as the default one
        $defaultPaymentMethod = $paymentMethods->data[0]->id;

        // Create a payment intent with the specified amount and currency
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

        // Log the payment intent for debugging
        error_log("Payment Intent created: " . json_encode($paymentIntent));

        // Create the response data to be sent back
        $response = [
            'success' => true,
            'payment_intent' => $paymentIntent->client_secret,
            'payment_method' => $defaultPaymentMethod
        ];

        // Log the response data before sending it
        error_log("Sending response: " . json_encode($response));

        // Send the response back to the client
        echo json_encode($response);
        exit;

    } catch (Exception $e) {
        // Handle exceptions and errors, send error response
        error_log("Payment processing error: " . $e->getMessage());

        $errorResponse = [
            'success' => false,
            'error' => $e->getMessage()
        ];

        // Log the error response before sending it
        error_log("Sending error response: " . json_encode($errorResponse));

        echo json_encode($errorResponse);
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
