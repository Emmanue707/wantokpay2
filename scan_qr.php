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
        // Initialize Stripe
        const stripe = Stripe('pk_test_51QhYByDUpDhJwyLXF2lYx388XY2itWsvCHxxIMs80XAAvHapt0nEp4DU3fANUji9tRYICQZpQON4xq4nANcPNKud00DbOoP1me');

            // Initialize QR Scanner with clean UI
            let html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: 250,
                    videoConstraints: {
                        facingMode: { exact: "environment" }
                    },
                    showTorchButtonIfSupported: false,
                    showZoomSliderIfSupported: false,
                    hideControls: true
                }
            );

            let isProcessing = false;

            function onScanSuccess(decodedText) {
                if (isProcessing) return;
                isProcessing = true;
    
                // Stop scanning immediately
                html5QrcodeScanner.clear();
    
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

            // Start scanning immediately
            html5QrcodeScanner.render(onScanSuccess);











                    // Show payment confirmation popup
                    const popup = document.createElement('div');
                    popup.className = 'payment-popup';
                    popup.innerHTML = `
                        <div class="payment-confirmation">
                            <h4>Payment Successful!</h4>
                            <p>Amount: K${qrData.amount}</p>
                            <p>Paid to: ${qrData.merchant_name}</p>
                        </div>
                    `;
                    document.body.appendChild(popup);

                    // Add popup styles
                    const style = document.createElement('style');
                    style.textContent = `
                        .payment-popup {
                            position: fixed;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            background: white;
                            padding: 20px;
                            border-radius: 8px;
                            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                            z-index: 1000;
                            animation: fadeIn 0.3s ease-out;
                        }
                        .payment-confirmation {
                            text-align: center;
                        }
                        @keyframes fadeIn {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }
                    `;
                    document.head.appendChild(style);

                    // Redirect after showing popup
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                }
            })
            .catch(error => {
                document.getElementById('payment-status').innerHTML = 

                    `<div class="alert alert-danger">Payment processing error. Please try again.</div>`;
            });

        }
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</body>
</html>
