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

<!-- Navigation icons at the top -->
<div class="icon-nav">
    <div class="container">
        <div class="icon-nav-items">
            <a href="#home" class="icon-nav-item active">
                <i class="bi bi-house-fill"></i>
            </a>
            <a href="#payment-methods" class="icon-nav-item">
                <i class="bi bi-credit-card-fill"></i>
            </a>
            <a href="#notifications" class="icon-nav-item">
                <i class="bi bi-bell-fill"></i>
            </a>
            <a href="#transactions" class="icon-nav-item">
                <i class="bi bi-clock-history"></i>
            </a>
        </div>
    </div>
</div>

<!-- Content sections -->
<div class="section-content" id="home">
    <!-- Quick Actions section -->
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
</div>

<!-- Section contents will appear here based on navigation -->
<div class="section-content" id="payment-methods" style="display: none;">
    <!-- Payment Methods card -->
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
</div>

<!-- Other sections follow here... -->

<footer>
    <div class="container text-center">
        <p>© 2025 WANTOK PAY. Developed by Waghi Tech.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Navigation script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconNavItems = document.querySelectorAll('.icon-nav-item');
    const sections = document.querySelectorAll('.section-content');

    // Show home section by default
    document.getElementById('home').style.display = 'block';

    iconNavItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove active class from all icons
            iconNavItems.forEach(icon => icon.classList.remove('active'));
            
            // Add active class to clicked icon
            item.classList.add('active');
            
            // Hide all sections
            sections.forEach(section => section.style.display = 'none');
            
            // Show selected section
            const targetId = item.getAttribute('href').substring(1);
            document.getElementById(targetId).style.display = 'block';
        });
    });
});
</script>
</body>
</html>
