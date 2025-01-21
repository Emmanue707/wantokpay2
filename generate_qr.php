<?php
session_start();
require_once 'Database.php';
require_once 'QRCode.php';
require_once __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$qrCodeImage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $qrData = [
        'merchant_id' => $_SESSION['user_id'],
        'amount' => $_POST['amount'],
        'description' => $_POST['description']
    ];

    $qrCode = new QrCode(json_encode($qrData));
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    $qrCodeImage = $result->getDataUri();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate QR - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Generate Payment QR Code</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-4">
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

                        <?php if ($qrCodeImage): ?>
                        <div class="text-center">
                            <h5 class="mb-3">Your QR Code</h5>
                            <img src="<?php echo $qrCodeImage; ?>" alt="Payment QR Code" class="img-fluid">
                            <p class="mt-3">Amount: K<?php echo number_format($_POST['amount'], 2); ?></p>
                            <p>Description: <?php echo htmlspecialchars($_POST['description']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
