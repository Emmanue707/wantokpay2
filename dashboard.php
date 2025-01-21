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

// Add the time_elapsed_string function here
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d < 1) {
        if ($diff->h < 1) {
            if ($diff->i < 1) {
                return 'Just now';
            }
            return $diff->i . ' min ago';
        }
        return $diff->h . ' hours ago';
    }
    if ($diff->d < 7) {
        return $diff->d . ' days ago';
    }
    return $ago->format('M j, Y');
}

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
    ORDER BY t.created_at DESC
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
<body class="dashboard-page">

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
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="userProfileBtn">
                        <i class="bi bi-person-circle fs-5"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Recent Transactions</h5>
                    <div class="d-flex gap-3">
                        <input type="text" id="searchTransactions" class="form-control" placeholder="Search transactions...">
                        <select id="timeFilter" class="form-select">
                            <option value="all">All Time</option>
                            <option value="30">Last 30 Days</option>
                            <option value="year">This Year</option>
                        </select>
                        <select id="typeFilter" class="form-select">
                            <option value="all">All Transactions</option>
                            <option value="sent">Sent Payments</option>
                            <option value="received">Received Payments</option>
                            <option value="qr">QR Payments</option>
                            <option value="manual">Manual Payments</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive transaction-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Details</th>
                                    <th>Amount</th>
                                    <th>Fee</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($transactions as $transaction): ?>
                                    <tr>
                                    <td>
                                        <?php 
                                                date_default_timezone_set('Pacific/Port_Moresby');
                                                $date = new DateTime($transaction['created_at']);
                                                echo $date->format('m/d/y H:i'); 
                                            ?>
                                        </td>
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
                                        </td>
                                        <td>K<?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td><?php echo $transaction['fee_amount'] ? 'K'.number_format($transaction['fee_amount'], 2) : '-'; ?></td>
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

<footer>
    <div class="container text-center">
        <p>© 2025 WANTOK PAY. All rights reserved.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTransactions');
    const timeFilter = document.getElementById('timeFilter');
    const typeFilter = document.getElementById('typeFilter');
    const transactionRows = document.querySelectorAll('.transaction-table tbody tr');

    function filterTransactions() {
        const searchTerm = searchInput.value.toLowerCase();
        const timeValue = timeFilter.value;
        const typeValue = typeFilter.value;
        const currentDate = new Date();

        transactionRows.forEach(row => {
            // Get row data
            const rowText = row.textContent.toLowerCase();
            const dateCell = row.querySelector('td:first-child').textContent;
            const transactionDate = new Date(dateCell);
            const typeCell = row.querySelector('.badge').textContent;
            const detailsCell = row.querySelector('td:nth-child(3)').textContent;

            // Search filter
            const matchesSearch = rowText.includes(searchTerm);

            // Time filter
            let matchesTime = true;
            if (timeValue === '30') {
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                matchesTime = transactionDate >= thirtyDaysAgo;
            } else if (timeValue === 'year') {
                matchesTime = transactionDate.getFullYear() === currentDate.getFullYear();
            }

            // Type filter
            let matchesType = true;
            if (typeValue === 'sent') {
                matchesType = detailsCell.includes('Paid to');
            } else if (typeValue === 'received') {
                matchesType = detailsCell.includes('Received from');
            } else if (typeValue === 'qr') {
                matchesType = typeCell.includes('QR');
            } else if (typeValue === 'manual') {
                matchesType = !typeCell.includes('QR');
            }

            // Show/hide row based on all filters
            row.style.display = (matchesSearch && matchesTime && matchesType) ? '' : 'none';
        });
    }

    // Add event listeners
    searchInput.addEventListener('input', filterTransactions);
    timeFilter.addEventListener('change', filterTransactions);
    typeFilter.addEventListener('change', filterTransactions);
});
</script>

</body>
</html>