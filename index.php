<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WANTOK PAY - Papua New Guinea's Digital Payment Solution</title>
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
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php else: ?>
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
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>


    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <h1>Welcome to WANTOK PAY</h1>
                <p class="lead">Papua New Guinea's most convenient digital payment solution</p>
                <div class="mt-4">
                    <h3>Features:</h3>
                    <ul>
                        <li>Send money instantly to friends and family</li>
                        <li>Pay with QR codes for small purchases (1-10 Kina)</li>
                        <li>Secure transactions with real-time tracking</li>
                        <li>Perfect for street vendors and small businesses</li>
                    </ul>
                    <a href="register.php" class="btn btn-primary btn-lg mt-3">Get Started</a>
                </div>
            </div>
            <div class="col-md-6">
                <img src="assets/images/wantok-pay-hero.png" alt="WANTOK PAY" class="img-fluid">
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>For Individual Users</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Send money, pay bills, and make purchases easily with your phone.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>For Businesses</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Accept payments, manage transactions, and grow your business.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>For Enterprises</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Custom solutions with advanced reporting and analytics.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
    <div class="container text-center">
        <p>&copy; 2024 WANTOK PAY. All rights reserved.</p>
    </div>
</footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
