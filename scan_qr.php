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
        const html5QrCode = new Html5Qrcode("reader");
        const config = {
            fps: 30, // Higher FPS for better scanning
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                // Play success sound
                new Audio('beep.mp3').play();
                
                // Parse QR data
                const qrData = JSON.parse(decodedText);
                
                // Process payment
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
                        window.location.href = 'dashboard.php';
                    }
                });
            }
        );

        // Hide unnecessary controls for the scanner
        setTimeout(() => {
            document.querySelectorAll('#reader__dashboard_section_csr button, #reader__dashboard_section_swaplink, #reader__header_message').forEach(el => {
                el.style.display = 'none';
            });
        }, 100);
    </script>
</body>
</html>
