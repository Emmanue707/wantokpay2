<?php
session_start();
require_once 'Database.php';
require_once '/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qr_data = json_decode($_POST['qr_data'], true);
    
    if ($qr_data && isset($qr_data['merchant_id']) && isset($qr_data['amount'])) {
        $database = new Database();
        $db = $database->getConnection();
        
        $user = new User($db);
        $user->id = $_SESSION['user_id'];
        
        if ($user->transfer($qr_data['merchant_id'], $qr_data['amount'])) {
            echo json_encode(['success' => true]);
            exit();
        }
    }
    
    echo json_encode(['success' => false]);
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
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
    <div class="container mt-4">
        <div id="reader"></div>
        <div id="result"></div>
    </div>
    
    <script>
        function onScanSuccess(decodedText) {
            document.getElementById('result').innerHTML = `QR Code detected: ${decodedText}`;
            // Send to server for processing
            fetch('scan_qr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `qr_data=${encodeURIComponent(decodedText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment successful!');
                    window.location.href = 'dashboard.php';
                }
            });
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</body>
</html>
