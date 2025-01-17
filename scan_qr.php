<?php
session_start();
require_once 'Database.php';
require_once 'User.php';
require_once 'vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">WANTOK PAY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Scan QR Code to Pay</h5>
                    </div>
                    <div class="card-body">
                        <div id="reader"></div>
                        <div id="payment-status" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Initialize Stripe and Elements
        const stripe = Stripe('pk_test_51QhYByDUpDhJwyLXF2lYx388XY2itWsvCHxxIMs80XAAvHapt0nEp4DU3fANUji9tRYICQZpQON4xq4nANcPNKud00DbOoP1me');
        const elements = stripe.elements();

        // Initialize QR Scanner
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
        function onScanSuccess(decodedText) {
            const qrData = JSON.parse(decodedText);
            
            fetch('process_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `qr_data=${encodeURIComponent(decodedText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    return stripe.confirmCardPayment(data.payment_intent, {
                        payment_method: data.payment_method
                    });
                }
                throw new Error('Payment failed');
            })
            })
            .then(result => {
                if (result.error) {
                    document.getElementById('payment-status').innerHTML = 
                        `<div class="alert alert-danger">${result.error.message}</div>`;
                } else {
                    document.getElementById('payment-status').innerHTML = 
                        '<div class="alert alert-success">Payment successful!</div>';
                    setTimeout(() => window.location.href = 'dashboard.php', 2000);
                }
            })
            .catch(error => {
                document.getElementById('payment-status').innerHTML = 
                    `<div class="alert alert-danger">${error.message}</div>`;
            });
        }
    </script>
</body>
</html>
