<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add these SEO meta tags -->
    <meta name="description" content="WANTOK PAY - The leading digital payment solution in Papua New Guinea. Fast, secure, and zero-fee transactions under 100 Kina. Instant QR payments for market purchases.">
    <meta name="keywords" content="wantok pay, papua new guinea payments, digital payments PNG, QR payments, mobile money PNG, instant payments">
    <meta name="author" content="Waghi Tech">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph tags for social sharing -->
    <meta property="og:title" content="WANTOK PAY - Modern Payment Solution">
    <meta property="og:description" content="Transform your digital payments in Papua New Guinea with fast, secure, and zero-fee transactions.">
    <meta property="og:image" content="[Your-Logo-URL]">
    <meta property="og:url" content="[Your-Website-URL]">
    
    <!-- Twitter Card tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="WANTOK PAY - Modern Payment Solution">
    <meta name="twitter:description" content="Transform your digital payments in Papua New Guinea with fast, secure, and zero-fee transactions.">
    
    <title>WANTOK PAY - Modern Payment Solution | Digital Payments Papua New Guinea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="landing-page">
    <!-- Navbar -->
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
<main role="main">
    <article class="hero-section">
        <div class="container">
            <header class="text-center hero-content">
                <h1 class="main-title">WANTOK PAY</h1>
                <p class="hero-subtitle">Transforming Digital Payments in Papua New Guinea</p>
            
                <!-- Login/Register buttons moved here -->
                <div class="cta-container">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn btn-glow btn-primary">Login</a>
                        <a href="register.php" class="btn btn-glow btn-outline">Register</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-glow btn-primary">Dashboard</a>
                    <?php endif; ?>
                </div>
            
                <!-- 3D Sliding Boxes -->
                <div class="slider-container">
                    <div class="slider">
                        <div class="slide">
                            <div class="slide-content">
                                <h3>Fast & Secure</h3>
                                <p>Instant payments with bank-grade security</p>
                            </div>
                        </div>
                        <div class="slide">
                            <div class="slide-content">
                                <h3>QR Payments</h3>
                                <p>Scan & pay instantly for market purchases</p>
                            </div>
                        </div>
                        <div class="slide">
                            <div class="slide-content">
                                <h3>Zero Fees</h3>
                                <p>No transaction fees under 100 Kina</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        </div>
    </article>
</main>
        </div>
    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>© 2025 WANTOK PAY. Developed by Waghi Tech.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FinancialService",
  "name": "WANTOK PAY",
  "description": "Digital payment solution in Papua New Guinea offering fast, secure transactions with zero fees under 100 Kina",
  "areaServed": "Papua New Guinea",
  "offers": {
    "@type": "Offer",
    "description": "Zero transaction fees under 100 Kina"
  }
}
</script>

</body>
</html>
