<?php
session_start();
require_once 'Database.php';
require_once 'vendor/autoload.php';

$db = new Database();
$db = $db->getConnection();

// Get payment details if token exists
$paymentDetails = null;
if (isset($_GET['token'])) {
    $stmt = $db->prepare("
        SELECT pl.*, u.username as merchant_name 
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
                        <?php if ($paymentDetails): ?>
                            <div class="payment-details mb-4">
                                <h6>Payment Request Details</h6>
                                <p>To: <?= htmlspecialchars($paymentDetails['merchant_name']) ?></p>
                                <p>Description: <?= htmlspecialchars($paymentDetails['description']) ?></p>
                            </div>
                        <?php endif; ?>

                        <form id="payment-form" method="POST" action="process_payment.php">
                            <?php if ($paymentDetails): ?>
                                <input type="hidden" name="token" value="<?= $_GET['token'] ?>">
                                <input type="hidden" name="recipient_username" value="<?= $paymentDetails['merchant_name'] ?>">
                            <?php else: ?>
                                <div class="mb-3">
                                    <label>Recipient Username</label>
                                    <input type="text" name="recipient_username" class="form-control" required>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label>Amount (K)</label>
                                <input type="number" 
                                       name="amount" 
                                       class="form-control" 
                                       value="<?= $paymentDetails ? $paymentDetails['amount'] : '' ?>"
                                       <?= $paymentDetails && !empty($paymentDetails['recipient_username']) ? 'readonly' : '' ?> 
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Send Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('payment-form').addEventListener('submit', async (e) => {
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
