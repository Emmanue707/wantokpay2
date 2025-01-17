<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WANTOK PAY - Modern Payment Solution</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="landing-page">
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-3 mb-4">WANTOK PAY</h1>
            <p class="lead mb-5">Transforming Digital Payments in Papua New Guinea</p>
            
            <div class="cta-buttons">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn btn-primary btn-lg me-3">Login</a>
                    <a href="register.php" class="btn btn-outline-primary btn-lg">Register</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                <?php endif; ?>
            </div>

            <div class="features-grid mt-5">
                <div class="feature-item">
                    <h3>Quick Payments</h3>
                    <p>Instant QR code payments for small purchases</p>
                </div>
                <div class="feature-item">
                    <h3>Secure</h3>
                    <p>Bank-grade security for all transactions</p>
                </div>
                <div class="feature-item">
                    <h3>Easy to Use</h3>
                    <p>Simple interface for vendors and customers</p>
                </div>
            </div>

            <div class="learn-more mt-5">
                <a href="about.php" class="btn btn-link">Learn more about Wantok Pay →</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
