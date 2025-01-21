<?php
require_once 'config/stripe_init.php';
require_once 'Database.php';

// Set error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'webhook_errors.log');

try {
    $stripe = new \Stripe\StripeClient('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');
    $endpoint_secret = 'whsec_Zd4XUA4TW3XD4BeLo9nr4oZU2lohclGs';

    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    
    // Verify webhook signature
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );

    $database = new Database();
    $db = $database->getConnection();

    // Handle the event
    switch ($event->type) {
        case 'payment_intent.succeeded':
            $paymentIntent = $event->data->object;
            if (isset($paymentIntent->metadata->qr_payment)) {
                $stmt = $db->prepare("UPDATE transactions SET status = 'completed' 
                                    WHERE id = ? AND status = 'pending'");
                $stmt->execute([$paymentIntent->metadata->transaction_id]);
            }
            break;

        case 'payment_intent.payment_failed':
            $paymentIntent = $event->data->object;
            $stmt = $db->prepare("UPDATE transactions SET status = 'failed' 
                                WHERE id = ? AND status = 'pending'");
            $stmt->execute([$paymentIntent->metadata->transaction_id]);
            break;

        case 'charge.succeeded':
            $charge = $event->data->object;
            $stmt = $db->prepare("UPDATE transactions SET status = 'completed' 
                                WHERE stripe_charge_id = ?");
            $stmt->execute([$charge->id]);
            break;

        default:
            error_log('Received unknown event type ' . $event->type);
    }

    // Return a 200 response to acknowledge receipt of the event
    http_response_code(200);
    echo json_encode(['status' => 'success']);

} catch(\UnexpectedValueException $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    error_log('Webhook signature verification failed: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch(\Exception $e) {
    error_log('Webhook general error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
