<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once 'Database.php';
$database = new Database();
$db = $database->getConnection();

// Get statistics
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('last monday'));
$month_start = date('Y-m-01');
$year_start = date('Y-01-01');

// Transactions count
$stmt = $db->prepare("SELECT 
    COUNT(*) as today_count,
    (SELECT COUNT(*) FROM transactions WHERE created_at >= ?) as week_count,
    (SELECT COUNT(*) FROM transactions WHERE created_at >= ?) as month_count,
    (SELECT COUNT(*) FROM transactions WHERE created_at >= ?) as year_count
    FROM transactions WHERE DATE(created_at) = ?");
$stmt->execute([$week_start, $month_start, $year_start, $today]);
$counts = $stmt->fetch(PDO::FETCH_ASSOC);

// Transaction amounts
$stmt = $db->prepare("SELECT 
    SUM(amount) as today_amount,
    (SELECT SUM(amount) FROM transactions WHERE created_at >= ?) as week_amount,
    (SELECT SUM(amount) FROM transactions WHERE created_at >= ?) as month_amount,
    (SELECT SUM(fee_amount) FROM transactions WHERE amount > 100) as total_fees
    FROM transactions WHERE DATE(created_at) = ?");
$stmt->execute([$week_start, $month_start, $today]);
$amounts = $stmt->fetch(PDO::FETCH_ASSOC);

// Get users list
$stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">WANTOK PAY ADMIN</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin_logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="icon-nav">
        <div class="container">
            <div class="icon-nav-items">
                <a href="#dashboard" class="icon-nav-item active">
                    <i class="bi bi-grid-fill"></i>
                </a>
                <a href="#users" class="icon-nav-item">
                    <i class="bi bi-people-fill"></i>
                </a>
                <a href="#transactions" class="icon-nav-item">
                    <i class="bi bi-cash-stack"></i>
                </a>
                <a href="#stats" class="icon-nav-item">
                    <i class="bi bi-bar-chart-fill"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Dashboard Overview -->
        <div class="section-content" id="dashboard">
            <div class="row">
                <div class="col-md-3">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <h5><i class="bi bi-calendar-day"></i> Today's Transactions</h5>
                            <h3><?php echo $counts['today_count']; ?></h3>
                            <p>K<?php echo number_format($amounts['today_amount'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <h5><i class="bi bi-calendar-week"></i> Weekly Transactions</h5>
                            <h3><?php echo $counts['week_count']; ?></h3>
                            <p>K<?php echo number_format($amounts['week_amount'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <h5><i class="bi bi-calendar-month"></i> Monthly Transactions</h5>
                            <h3><?php echo $counts['month_count']; ?></h3>
                            <p>K<?php echo number_format($amounts['month_amount'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <h5><i class="bi bi-cash"></i> Total Fees Collected</h5>
                            <h3>K<?php echo number_format($amounts['total_fees'], 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Management -->
        <div class="section-content" id="users" style="display: none;">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5><i class="bi bi-people"></i> User Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                        <td>
    <span class="badge bg-<?php echo $user['is_disabled'] ? 'danger' : 'success'; ?>">
        <?php echo $user['is_disabled'] ? 'Disabled' : 'Active'; ?>
    </span>
</td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="toggleUserStatus(<?php echo $user['id']; ?>)">
                                                <i class="bi bi-toggle-on"></i>
                                            </button>
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
    <footer>
        <div class="container text-center">
            <p>© 2025 WANTOK PAY Admin Panel. Developed by Waghi Tech.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation Script
        document.addEventListener('DOMContentLoaded', function() {
            const iconNavItems = document.querySelectorAll('.icon-nav-item');
            const sections = document.querySelectorAll('.section-content');

            iconNavItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    iconNavItems.forEach(icon => icon.classList.remove('active'));
                    item.classList.add('active');
                    sections.forEach(section => section.style.display = 'none');
                    const targetId = item.getAttribute('href').substring(1);
                    document.getElementById(targetId).style.display = 'block';
                });
            });
        });

        // User Management Functions
        function deleteUser(userId) {
            if(confirm('Are you sure you want to delete this user?')) {
                // Add AJAX call to delete user
            }
        }
            function toggleUserStatus(userId) {
                $.ajax({
                    url: 'toggle_user_status.php',
                    type: 'POST',
                    data: { user_id: userId },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error updating user status');
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>
