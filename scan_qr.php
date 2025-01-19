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

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <title>Scan QR - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .scan-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            font-family: 'Poppins', sans-serif;
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
            width: 300px;
            height: 300px;
            border: 3px solid rgba(255,255,255,0.8);
            border-radius: 25px;
            box-shadow: 0 0 0 100vmax rgba(0,0,0,0.5);
            animation: pulse 2s infinite;
        }
        
        .back-button {
            position: fixed;
            top: 30px;
            left: 30px;
            z-index: 1000;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .scan-text {
            position: fixed;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 2rem;
            font-weight: 600;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">
        <i class="bi bi-arrow-left-circle-fill"></i> Back
    </a>
    <div class="scan-container">
        <div id="reader"></div>
        <div class="scan-overlay"></div>
        <div class="scan-text">
            <i class="bi bi-qr-code-scan" style="font-size: 3rem;"></i>
            <div>Scanning...</div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode;

        // Function to start camera with a fallback
        const startCamera = async () => {
            try {
                // Initialize QR scanner with minimal configuration
                html5QrCode = new Html5Qrcode("reader");

                // Simple camera start
                await html5QrCode.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: 250,
                    },
                    (decodedText, decodedResult) => {
                        // Handle success
                        onScanSuccess(decodedText);
                    },
                    (errorMessage) => {
                        // Keep scanning
                    }
                );
            } catch (err) {
                console.log("Camera start error:", err);
                alert('Unable to access camera. Please check permissions.');
            }
        };

        // Start camera when page loads
        document.addEventListener('DOMContentLoaded', startCamera);

        // Hide unnecessary controls for the scanner
        setTimeout(() => {
            document.querySelectorAll('#reader__dashboard_section_csr button, #reader__dashboard_section_swaplink, #reader__header_message').forEach(el => {
                el.style.display = 'none';
            });
        }, 100);
        
        // Function to handle successful scan
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
                alert('Payment processing error. Please try again.');
            });
        }
    </script>
</body>
</html>
