<?php
session_start();
require_once 'Database.php';
require_once 'vendor/autoload.php';

$db = new Database();
$db = $db->getConnection();

$paymentDetails = null;
if (isset($_GET['token'])) {
    $stmt = $db->prepare("
        SELECT pl.*, u.username as merchant_name, u.email as merchant_email
        FROM payment_links pl 
        JOIN users u ON pl.merchant_id = u.id 
        WHERE pl.link_token = ? AND pl.status = 'active'
    ");
    $stmt->execute([$_GET['token']]);
    $paymentDetails = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Money - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .payment-link-input {
            background: #173A5E;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .payment-details {
            background: rgba(102, 178, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .payment-details p {
            color: #66B2FF;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="dashboard-page">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Send Payment</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$paymentDetails): ?>
                            <div class="payment-link-input">
                                <form id="linkForm">
                                    <div class="mb-3">
                                        <label>Paste Payment Link</label>
                                        <input type="text" class="form-control" id="paymentLink" placeholder="https://...">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Load Payment Details</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if ($paymentDetails): ?>
                            <div class="payment-details">
                                <h6>Payment Request Details</h6>
                                <p><strong>To:</strong> <?= htmlspecialchars($paymentDetails['merchant_name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($paymentDetails['merchant_email']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($paymentDetails['description']) ?></p>
                            </div>

                            <form id="payment-form" method="POST">
                                <input type="hidden" name="token" value="<?= $_GET['token'] ?>">
                                <input type="hidden" name="recipient_username" value="<?= $paymentDetails['merchant_name'] ?>">

                                <div class="mb-3">
                                    <label>Amount (K)</label>
                                    <input type="number" 
                                           name="amount" 
                                           class="form-control" 
                                           value="<?= $paymentDetails['amount'] ?>"
                                           <?= !empty($paymentDetails['recipient_username']) ? 'readonly' : '' ?> 
                                           required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Confirm Payment</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('linkForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const link = document.getElementById('paymentLink').value;
            const token = new URL(link).searchParams.get('token');
            if (token) {
                window.location.href = `send_money.php?token=${token}`;
            }
        });

        document.getElementById('payment-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    window.location.href = 'dashboard.php?payment=success';
                } else {
                    alert(result.error || 'Payment failed');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
