<?php
require_once 'config/stripe_init.php';
require_once 'Database.php';

$stripe = new \Stripe\StripeClient('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');
$endpoint_secret = 'whsec_Zd4XUA4TW3XD4BeLo9nr4oZU2lohclGs';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );

    error_log('Webhook received: ' . $event->type);

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

                             case 'payment_method.attached':
                                 $paymentMethod = $event->data->object;
                                 $stmt = $db->prepare("UPDATE users SET has_payment_method = 1 WHERE stripe_customer_id = ?");
                                 $stmt->execute([$paymentMethod->customer]);
                                 error_log('Payment method attached for customer: ' . $paymentMethod->customer);
                                 break;
            
            case 'payment_method.detached':
                $paymentMethod = $event->data->object;
                $stmt = $db->prepare("UPDATE users SET has_payment_method = 0 
                                     WHERE stripe_customer_id = ?");
                $stmt->execute([$paymentMethod->customer]);
                break;
            

        case 'customer.subscription.created':
            $subscription = $event->data->object;
            $stmt = $db->prepare("INSERT INTO subscriptions (user_id, stripe_subscription_id, status) VALUES (?, ?, 'active')");
            $stmt->execute([$subscription->metadata->user_id, $subscription->id]);
            break;

        case 'customer.subscription.updated':
            $subscription = $event->data->object;
            $stmt = $db->prepare("UPDATE subscriptions SET status = ? WHERE stripe_subscription_id = ?");
            $stmt->execute([$subscription->status, $subscription->id]);
            break;

        case 'invoice.payment_succeeded':
            $invoice = $event->data->object;
            $stmt = $db->prepare("INSERT INTO payments (user_id, amount, stripe_invoice_id) VALUES (?, ?, ?)");
            $stmt->execute([$invoice->customer, $invoice->amount_paid, $invoice->id]);
            break;

        case 'customer.dispute.created':
            $dispute = $event->data->object;
            $stmt = $db->prepare("UPDATE transactions SET status = 'disputed' WHERE stripe_charge_id = ?");
            $stmt->execute([$dispute->charge]);
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
