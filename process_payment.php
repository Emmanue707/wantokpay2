<?php
session_start();
require_once 'Database.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');

$db = new Database();
$db = $db->getConnection();

try {
    // Handle QR code payments
    if (isset($_POST['qr_data'])) {
        $qrData = json_decode($_POST['qr_data'], true);
        
        // Get customer payment info
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get customer's default payment method
        $paymentMethods = \Stripe\PaymentMethod::all([
            'customer' => $user['stripe_customer_id'],
            'type' => 'card',
        ]);
        $defaultPaymentMethod = $paymentMethods->data[0]->id;
        
        // Calculate base amount and fee for QR payment
        $baseAmount = $qrData['amount'];
        $fee = ($baseAmount >= 100) ? $baseAmount * 0.05 : 0;
        $totalAmount = ($baseAmount + $fee) * 100;
        
        // Create payment intent for QR payment
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $totalAmount,
            'currency' => 'pgk',
            'customer' => $user['stripe_customer_id'],
            'payment_method' => $defaultPaymentMethod,
            'payment_method_types' => ['card'],
            'off_session' => true,
            'confirm' => true,
            'metadata' => [
                'qr_payment' => true,
                'base_amount' => $baseAmount,
                'fee_amount' => $fee,
                'merchant_id' => $qrData['merchant_id']
            ]
        ]);
        
        // Record QR transaction
        $stmt = $db->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, type, status) VALUES (?, ?, ?, 'qr_payment', 'completed')");
        $stmt->execute([$_SESSION['user_id'], $qrData['merchant_id'], $baseAmount]);
        
        echo json_encode([
            'success' => true,
            'payment_intent' => $paymentIntent->client_secret,
            'payment_method' => $defaultPaymentMethod,
            'amount' => $totalAmount / 100
        ]);
        exit;
    }

    // Handle link payments
    if (isset($_POST['token'])) {
        // Get payment link details
        $stmt = $db->prepare("SELECT * FROM payment_links WHERE link_token = ? AND status = 'active'");
        $stmt->execute([$_POST['token']]);
        $paymentLink = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paymentLink) {
            throw new Exception('Invalid or expired payment link');
        }
    }

    // Calculate fee (5% for amounts >= 100)
    $amount = floatval($_POST['amount']);
    $fee = ($amount >= 100) ? $amount * 0.05 : 0;
    $totalAmount = ($amount + $fee) * 100; // Convert to cents

    // Create payment intent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $totalAmount,
        'currency' => 'pgk',
        'payment_method' => $_POST['payment_method_id'],
        'confirm' => true,
        'metadata' => [
            'fee_amount' => $fee,
            'payment_link_id' => $paymentLink['id'] ?? null
        ]
    ]);

    // Record transaction
    $db->beginTransaction();

    // Update payment link status
    if (isset($paymentLink)) {
        $stmt = $db->prepare("UPDATE payment_links SET status = 'used' WHERE id = ?");
        $stmt->execute([$paymentLink['id']]);

        // Mark notification as read if exists
        if ($paymentLink['recipient_username']) {
            $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE WHERE link_token = ?");
            $stmt->execute([$_POST['token']]);
        }
    }

    // Record transaction
    $stmt = $db->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, fee_amount, type, status) VALUES (?, ?, ?, ?, 'card_payment', 'completed')");
    $stmt->execute([
        $_SESSION['user_id'],
        $paymentLink['merchant_id'] ?? null,
        $amount,
        $fee
    ]);

    $db->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
