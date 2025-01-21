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



        <div class="row mb-4">
            <div class="col-md-12">
            <div class="card payment-method-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Payment Methods</h5>
                            <?php if ($user['stripe_customer_id']): ?>
                                <div class="status-indicator card-linked">
                                    <i class="bi bi-credit-card-fill text-success"></i>
                                    <span class="text-success">Card Active & Ready</span>
                                </div>
                            <?php else: ?>
                                <div class="status-indicator">
                                    <i class="bi bi-exclamation-circle text-warning"></i>
                                    <span class="text-warning">No Card Linked</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if (!$user['stripe_customer_id']): ?>
                                <a href="link_card.php" class="btn btn-link-card">Link Card</a>
                            <?php else: ?>
                                <a href="link_card.php" class="btn btn-link-card">Update Card</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="card dashboard-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Notifications</h5>
        <div>
            <span class="badge bg-primary" id="unreadCount"></span>
            <button class="btn btn-sm btn-outline-light ms-2" onclick="markAllAsRead()">Mark All Read</button>
        </div>
    </div>
    <div class="card-body">
        <div class="notifications-container">
            <?php
            $stmt = $db->prepare("
                SELECT n.*, 
                       u.username as sender_name,
                       t.status as transaction_status,
                       t.type as transaction_type
                FROM notifications n
                LEFT JOIN users u ON u.id = n.user_id
                LEFT JOIN transactions t ON t.id = n.transaction_id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($notifications as $notification): 
                $isUnread = $notification['status'] === 'unread';
                $isPaid = $notification['transaction_status'] === 'completed';
                $timeAgo = time_elapsed_string($notification['created_at']);
                $icon = getNotificationIcon($notification['payment_type']);
            ?>
                <div class="notification-item <?= $isUnread ? 'unread' : '' ?>" 
                     onclick="markAsRead(<?= $notification['id'] ?>)">
                    <div class="notification-icon">
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <div class="notification-content">
                        <div class="d-flex justify-content-between">
                            <h6><?= htmlspecialchars($notification['message']) ?></h6>
                            <small class="notification-time"><?= $timeAgo ?></small>
                        </div>
                        <?php if($notification['type'] === 'payment_request'): ?>
                            <?php if(!$isPaid): ?>
                                <a href="send_money.php?token=<?= $notification['link_token'] ?>" 
                                   class="btn btn-primary btn-sm mt-2">Pay Now</a>
                            <?php else: ?>
                                <span class="badge bg-success">Paid</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch('mark_notification_read.php', {
        method: 'POST',
        body: JSON.stringify({ notification_id: notificationId })
    });
}

function markAllAsRead() {
    fetch('mark_all_notifications_read.php', {
        method: 'POST'
    }).then(() => {
        document.querySelectorAll('.notification-item.unread')
            .forEach(item => item.classList.remove('unread'));
    });
}

function getNotificationIcon(type) {
    const icons = {
        'qr_payment': 'bi-qr-code',
        'manual_transfer': 'bi-cash',
        'payment_request': 'bi-envelope',
        'default': 'bi-bell'
    };
    return icons[type] || icons.default;
}
</script>
        <div class="row mb-4">
            <div class="col-md-12">
            <div class="card dashboard-card">
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
                        <a href="generate_link.php" class="btn btn-info flex-fill">
                            <i class="bi bi-link-45deg"></i> Request Payment
                        </a>
                        <a href="send_money.php" class="btn btn-warning flex-fill">
                            <i class="bi bi-link"></i> Manual Payment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <div class="row">
            <div class="col-md-12">
            <div class="card dashboard-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Recent Transactions</h5>
                            <div class="d-flex gap-3">
                                <input type="text" id="searchTransactions" class="form-control" placeholder="Search transactions...">
                                <select id="timeFilter" class="form-select">
                                    <option value="all">All Time</option>
                                    <option value="30">Last 30 Days</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
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
    </div>    <div class="modal fade" id="userProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Profile Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle display-1"></i>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Username:</label>
                    <p id="profileUsername"></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Email:</label>
                    <p id="profileEmail"></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Member Since:</label>
                    <p id="profileCreatedAt"></p>
                </div>
            </div>
        </div>
    </div>
</div>
        </div> <!-- Close last container -->
    </div> <!-- Close any remaining open divs -->
    
    <footer>
        <div class="container text-center">
            <p>© 2025 WANTOK PAY. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.getElementById('userProfileBtn').addEventListener('click', async () => {
    try {
        const response = await fetch('user_details.php');
        const userData = await response.json();
        
        document.getElementById('profileUsername').textContent = userData.username;
        document.getElementById('profileEmail').textContent = userData.email;
        document.getElementById('profileCreatedAt').textContent = new Date(userData.created_at).toLocaleDateString();
        
        new bootstrap.Modal(document.getElementById('userProfileModal')).show();
    } catch (error) {
        console.error('Error fetching user details:', error);
    }
});

const searchInput = document.getElementById('searchTransactions');
const timeFilter = document.getElementById('timeFilter');
const transactionRows = document.querySelectorAll('tbody tr');

function filterTransactions() {
    const searchTerm = searchInput.value.toLowerCase();
    const filterValue = timeFilter.value;
    const currentDate = new Date();

    transactionRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const date = new Date(row.querySelector('td:first-child').textContent);
        let showByDate = true;

        if (filterValue === '30') {
            const thirtyDaysAgo = new Date(currentDate.setDate(currentDate.getDate() - 30));
            showByDate = date >= thirtyDaysAgo;
        } else if (filterValue === 'year') {
            showByDate = date.getFullYear() === currentDate.getFullYear();
        }

        const showBySearch = text.includes(searchTerm);
        row.style.display = (showBySearch && showByDate) ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterTransactions);
timeFilter.addEventListener('change', filterTransactions);
</script>

</body>
</html>
