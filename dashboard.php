<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'Database.php';
require_once 'User.php';

$database = new Database();
$db = $database->getConnection();

// Get user's card status
$stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent transactions with detailed information
$stmt = $db->prepare("
    SELECT 
        t.*,
        u1.username as sender_name,
        u2.username as receiver_name,
        qr.description as qr_description
    FROM transactions t
    LEFT JOIN users u1 ON t.sender_id = u1.id
    LEFT JOIN users u2 ON t.receiver_id = u2.id
    LEFT JOIN qr_codes qr ON t.type = 'qr_payment' AND t.receiver_id = qr.merchant_id
    WHERE t.sender_id = ? OR t.receiver_id = ?
    ORDER BY t.created_at DESC LIMIT 15
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
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
            </ul>
        </div>
    </div>
</nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">Payment Methods</h5>
                                <?php if ($user['stripe_customer_id']): ?>
                                    <p class="text-success mb-0"><i class="bi bi-credit-card"></i> Card Linked</p>
                                <?php else: ?>
                                    <p class="text-warning mb-0"><i class="bi bi-exclamation-circle"></i> No Card Linked</p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if (!$user['stripe_customer_id']): ?>
                                    <a href="link_card.php" class="btn btn-primary">Link Card</a>
                                <?php else: ?>
                                    <a href="link_card.php" class="btn btn-outline-primary">Update Card</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <a href="scan_qr.php" class="btn btn-primary flex-fill">
                                <i class="bi bi-qr-code-scan"></i> Scan to Pay
                            </a>
                            <a href="generate_qr.php" class="btn btn-success flex-fill">
                                <i class="bi bi-qr-code"></i> Generate QR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Details</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $transaction['type'] === 'qr_payment' ? 'success' : 'primary'; ?>">
                                                    <?php echo $transaction['type'] === 'qr_payment' ? 'QR Payment' : 'Card Payment'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($transaction['sender_id'] == $_SESSION['user_id']): ?>
                                                    <span class="text-danger">Paid to <?php echo htmlspecialchars($transaction['receiver_name']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-success">Received from <?php echo htmlspecialchars($transaction['sender_name']); ?></span>
                                                <?php endif; ?>
                                                <?php if($transaction['qr_description']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($transaction['qr_description']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>K<?php echo number_format($transaction['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $transaction['status'] === 'completed' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($transaction['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
