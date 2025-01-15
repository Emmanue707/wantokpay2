<?php
session_start();
require_once 'Database.php';
require_once 'User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qr_data = json_decode($_POST['qr_data'], true);
    
    if ($qr_data && isset($qr_data['merchant_id']) && isset($qr_data['amount'])) {
        $database = new Database();
        $db = $database->getConnection();
        
        $user = new User($db);
        $user->id = $_SESSION['user_id'];
        
        if ($user->transfer($qr_data['merchant_id'], $qr_data['amount'])) {
            // Record the QR payment transaction
            $stmt = $db->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, type, status) 
                                VALUES (?, ?, ?, 'qr_payment', 'completed')");
            $stmt->execute([$_SESSION['user_id'], $qr_data['merchant_id'], $qr_data['amount']]);
            
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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
