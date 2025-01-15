<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'Database.php';
require_once '/User.php';
require_once '/QRCode.php';

$database = new Database();
$db = $database->getConnection();

// Get user balance and recent transactions
$stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent transactions
$stmt = $db->prepare("
    SELECT t.*, 
           u1.username as sender_name,
           u2.username as receiver_name
    FROM transactions t
    LEFT JOIN users u1 ON t.sender_id = u1.id
    LEFT JOIN users u2 ON t.receiver_id = u2.id
    WHERE t.sender_id = ? OR t.receiver_id = ?
    ORDER BY t.created_at DESC LIMIT 10
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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">WANTOK PAY</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Balance</h5>
                        <h2 class="card-text">K<?php echo number_format($user['balance'], 2); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendMoneyModal">
                                Send Money
                            </button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#scanQRModal">
                                Scan QR Code
                            </button>
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#generateQRModal">
                                Generate QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>From/To</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                                            <td><?php echo $transaction['type']; ?></td>
                                            <td>
                                                <?php 
                                                if($transaction['sender_id'] == $_SESSION['user_id']) {
                                                    echo "To: " . $transaction['receiver_name'];
                                                } else {
                                                    echo "From: " . $transaction['sender_name'];
                                                }
                                                ?>
                                            </td>
                                            <td>K<?php echo number_format($transaction['amount'], 2); ?></td>
                                            <td><?php echo $transaction['status']; ?></td>
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

    <!-- Send Money Modal -->
    <div class="modal fade" id="sendMoneyModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Money</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="send_money.php" method="POST">
                        <div class="mb-3">
                            <label>Recipient Email</label>
                            <input type="email" name="recipient_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Amount (K)</label>
                            <input type="number" name="amount" step="0.01" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Money</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
