<?php
require_once 'config/stripe_init.php';
require_once 'Database.php';

$stripe = new \Stripe\StripeClient('your_stripe_secret_key');
$endpoint_secret = 'whsec_4a0c9ab898e8d138c8c1748adf25a73b753ed9d57635dedfe256c7693a827f4e';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );

    $database = new Database();
    $db = $database->getConnection();

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
            // Handle successful charge
            $stmt = $db->prepare("UPDATE transactions SET status = 'completed' 
                                WHERE stripe_charge_id = ?");
            $stmt->execute([$charge->id]);
            break;

        case 'charge.failed':
            $charge = $event->data->object;
            // Handle failed charge
            $stmt = $db->prepare("UPDATE transactions SET status = 'failed' 
                                WHERE stripe_charge_id = ?");
            $stmt->execute([$charge->id]);
            break;

        case 'transfer.created':
            $transfer = $event->data->object;
            // Handle transfer creation
            $stmt = $db->prepare("INSERT INTO transfers (stripe_transfer_id, amount, status) 
                                VALUES (?, ?, 'created')");
            $stmt->execute([$transfer->id, $transfer->amount / 100]);
            break;

        case 'transfer.paid':
            $transfer = $event->data->object;
            // Handle successful transfer
            $stmt = $db->prepare("UPDATE transfers SET status = 'completed' 
                                WHERE stripe_transfer_id = ?");
            $stmt->execute([$transfer->id]);
            break;

        default:
            // Log unknown event types for monitoring
            error_log('Received unknown event type ' . $event->type);
    }

    http_response_code(200);
} catch(\UnexpectedValueException $e) {
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
} catch(\Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit();
}
