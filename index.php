<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WANTOK PAY - Modern Payment Solution</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="landing-page">
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="hero-section">
        <div class="container">
            <div class="text-center hero-content">
                <h1 class="main-title">WANTOK PAY</h1>
                <p class="hero-subtitle">Transforming Digital Payments in Papua New Guinea</p>
                
                <div class="cta-container">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn btn-glow btn-primary">Login</a>
                        <a href="register.php" class="btn btn-glow btn-outline">Register</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-glow btn-primary">Dashboard</a>
                    <?php endif; ?>
                </div>

                <div class="info-cards">
                    <div class="info-card">
                        <div class="card-icon">💳</div>
                        <h3>Quick & Easy</h3>
                        <p>Instant payments with QR codes</p>
                    </div>
                    <div class="info-card">
                        <div class="card-icon">🔒</div>
                        <h3>Secure</h3>
                        <p>Bank-grade security</p>
                    </div>
                    <div class="info-card">
                        <div class="card-icon">📱</div>
                        <h3>Mobile First</h3>
                        <p>Pay anywhere, anytime</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
