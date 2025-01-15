<?php
session_start();
require_once 'Database.php';
require_once '/QRCode.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $qrGenerator = new QRCodeGenerator($db);
    $qrCode = $qrGenerator->generatePaymentQR(
        $_SESSION['user_id'],
        $_POST['amount'],
        $_POST['description']
    );
    
    if ($qrCode) {
        header('Content-Type: image/png');
        echo $qrCode;
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate QR - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5>Generate Payment QR Code</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Amount (K)</label>
                        <input type="number" name="amount" step="0.01" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate QR Code</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
