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
    <style>
        .scan-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
        }
        
        #reader {
            position: relative;
            width: 100%;
            height: 100vh;
            background: transparent;
        }
        
        .scan-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 280px;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 20px;
            box-shadow: 0 0 0 100vmax rgba(0,0,0,0.5);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 100vmax rgba(0,0,0,0.5); }
            50% { box-shadow: 0 0 0 100vmax rgba(0,0,0,0.3); }
            100% { box-shadow: 0 0 0 100vmax rgba(0,0,0,0.5); }
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">← Back</a>
    <div class="scan-container">
        <div id="reader"></div>
        <div class="scan-overlay"></div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10,
                qrbox: 250,
                videoConstraints: {
                    facingMode: { exact: "environment" }
                },
                showTorchButtonIfSupported: true,
                rememberLastUsedCamera: true
            },
            false
        );
        
        html5QrcodeScanner.render(onScanSuccess);
        
        // Hide all HTML5QR scanner controls
        setTimeout(() => {
            document.querySelectorAll('#reader__dashboard_section_csr button, #reader__dashboard_section_swaplink, #reader__header_message').forEach(el => {
                el.style.display = 'none';
            });
        }, 100);
        
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
    </script>
</body>
</html>
