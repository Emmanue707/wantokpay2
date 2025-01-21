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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        .payment-section {
            background: #173A5E;
            padding: 20px;
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
        .section-title {
            color: #66B2FF;
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .divider {
            border-top: 1px solid rgba(102, 178, 255, 0.2);
            margin: 30px 0;
        }
    </style>
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
                <?php if(isset($_SESSION['user_id'])): ?>
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
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                <?php endif; ?>
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
                        <?php if (!$paymentDetails): ?>
                            <!-- Payment Link Section -->
                            <div class="payment-section">
                                <h6 class="section-title">Payment Link</h6>
                                <form id="linkForm">
                                    <div class="mb-3">
                                        <label>Paste Payment Link</label>
                                        <input type="text" class="form-control" id="paymentLink" placeholder="https://...">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Load Payment Details</button>
                                </form>
                            </div>

                            <div class="divider"></div>

                            <!-- Custom Payment Section -->
                            <div class="payment-section">
                                <h6 class="section-title">Custom Payment</h6>
                                <form id="customPaymentForm">
                                    <div class="mb-3">
                                        <label>Username</label>
                                        <input type="text" name="recipient_username" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Amount (K)</label>
                                        <input type="number" name="amount" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Description</label>
                                        <input type="text" name="description" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Send Payment</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="payment-details">
                                <h6>Payment Request Details</h6>
                                <p><strong>To:</strong> <?= htmlspecialchars($paymentDetails['merchant_name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($paymentDetails['merchant_email']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($paymentDetails['description']) ?></p>
                            </div>

                            <form id="payment-form">
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

        const handlePaymentSubmit = async (formElement, e) => {
            e.preventDefault();
            const formData = new FormData(formElement);

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
        };

        document.getElementById('payment-form')?.addEventListener('submit', 
            (e) => handlePaymentSubmit(e.target, e));
        document.getElementById('customPaymentForm')?.addEventListener('submit', 
            (e) => handlePaymentSubmit(e.target, e));
            
    </script>
</body>
</html>
