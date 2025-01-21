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
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="dashboard-page">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">WANTOK PAY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="scan_qr.php">Scan QR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="generate_qr.php">Generate QR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

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
                                <p>From: <?= htmlspecialchars($paymentDetails['merchant_name']) ?></p>
                                <p>Description: <?= htmlspecialchars($paymentDetails['description']) ?></p>
                            </div>
                        <?php endif; ?>

                        <form id="payment-form">
                            <?php if ($paymentDetails): ?>
                                <input type="hidden" name="token" value="<?= $_GET['token'] ?>">
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

                            <div id="card-element" class="form-control mb-3">
                                <!-- Stripe card element -->
                            </div>
                            <div id="card-errors" class="text-danger mb-3"></div>

                            <button type="submit" class="btn btn-primary w-100">Send Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const stripe = Stripe('pk_test_51QhYByDUpDhJwyLXF2lYx388XY2itWsvCHxxIMs80XAAvHapt0nEp4DU3fANUji9tRYICQZpQON4xq4nANcPNKud00DbOoP1me');
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#card-element');

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const {error, paymentMethod} = await stripe.createPaymentMethod({
                type: 'card',
                card: card,
            });

            if (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
                return;
            }

            // Create payment
            const formData = new FormData(form);
            formData.append('payment_method_id', paymentMethod.id);

            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = 'dashboard.php?payment=success';
                } else {
                    document.getElementById('card-errors').textContent = result.error;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
