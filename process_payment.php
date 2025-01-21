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
        
        $transactionId = $db->lastInsertId();
    
        // Create notification for QR payment received
        $message = "You received K{$baseAmount} via QR Payment";
        $stmt = $db->prepare("INSERT INTO notifications (
            user_id, 
            type, 
            message, 
            amount, 
            payment_type, 
            transaction_id, 
            status
        ) VALUES (?, 'payment_received', ?, ?, 'qr_payment', ?, 'unread')");
        $stmt->execute([
            $qrData['merchant_id'], 
            $message, 
            $baseAmount, 
            $transactionId
        ]);
        
        echo json_encode([
            'success' => true,
            'payment_intent' => $paymentIntent->client_secret,
            'payment_method' => $defaultPaymentMethod,
            'amount' => $totalAmount / 100
        ]);
        exit;
    }
    // Handle username-based payments
    if (isset($_POST['recipient_username'])) {
        // Get recipient's details
        $stmt = $db->prepare("SELECT id, username FROM users WHERE username = ?");
        $stmt->execute([$_POST['recipient_username']]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipient) {
            throw new Exception('Recipient not found');
        }

        // Get sender's payment info
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $sender = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate amount and fee
        $amount = floatval($_POST['amount']);
        $fee = ($amount >= 100) ? $amount * 0.05 : 0;
        $totalAmount = ($amount + $fee) * 100;

        // Get sender's default payment method
        $paymentMethods = \Stripe\PaymentMethod::all([
            'customer' => $sender['stripe_customer_id'],
            'type' => 'card',
        ]);
        $defaultPaymentMethod = $paymentMethods->data[0]->id;

        // Create and confirm payment
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $totalAmount,
            'currency' => 'pgk',
            'customer' => $sender['stripe_customer_id'],
            'payment_method' => $defaultPaymentMethod,
            'off_session' => true,
            'confirm' => true,
            'metadata' => [
                'sender_id' => $_SESSION['user_id'],
                'recipient_id' => $recipient['id'],
                'fee_amount' => $fee
            ]
        ]);

        // Record transaction
        $db->beginTransaction();

        // Update payment link status if token exists
        if (isset($_POST['token'])) {
            $stmt = $db->prepare("UPDATE payment_links SET status = 'used' WHERE link_token = ?");
            $stmt->execute([$_POST['token']]);

            // Mark notification as read
            $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE WHERE link_token = ?");
            $stmt->execute([$_POST['token']]);
        }

        // Insert transaction record
        $stmt = $db->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, fee_amount, type, status) VALUES (?, ?, ?, ?, 'card_payment', 'completed')");
        $stmt->execute([$_SESSION['user_id'], $recipient['id'], $amount, $fee]);

        $db->commit();

        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Add this after successful transaction creation
function createPaymentNotification($db, $receiverId, $amount, $paymentType, $transactionId, $senderId) {
    $senderStmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $senderStmt->execute([$senderId]);
    $sender = $senderStmt->fetch(PDO::FETCH_ASSOC);

    $message = "You received K{$amount} from {$sender['username']} via " . ucfirst($paymentType);
    
    $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, amount, payment_type, transaction_id, status) 
                         VALUES (?, 'payment_received', ?, ?, ?, ?, 'unread')");
    $stmt->execute([$receiverId, $message, $amount, $paymentType, $transactionId]);
}

// Add this after successful transaction insertion
$transactionId = $db->lastInsertId();
createPaymentNotification($db, $recipient['id'], $amount, 'manual_transfer', $transactionId, $_SESSION['user_id']);
