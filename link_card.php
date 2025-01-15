<?php
session_start();

// Database connection and email fetch
$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$_SESSION['email'] = $user['email'];

require_once 'Database.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('your_stripe_secret_key');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $customer = \Stripe\Customer::create([
            'source' => $_POST['stripeToken'],
            'email' => $_SESSION['email']
        ]);

        $db = (new Database())->getConnection();
        $stmt = $db->prepare("UPDATE users SET stripe_customer_id = ? WHERE id = ?");
        $stmt->execute([$customer->id, $_SESSION['user_id']]);

        $_SESSION['success'] = "Card linked successfully!";
        header("Location: dashboard.php");
        exit();
    } catch (\Stripe\Exception\CardException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Card - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Link Your Card</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form id="payment-form" method="POST" action="link_card.php">
                            <div class="mb-3">
                                <div id="card-element" class="form-control"></div>
                                <div id="card-errors" class="text-danger mt-2"></div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Link Card</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        console.log('Page loaded');
        console.log('Stripe script loaded');
        const stripe = Stripe('pk_test_51QhYByDUpDhJwyLXF2lYx388XY2itWsvCHxxIMs80XAAvHapt0nEp4DU3fANUji9tRYICQZpQON4xq4nANcPNKud00DbOoP1me');
        const elements = stripe.elements();
        const card = elements.create('card');
        console.log('Card element created');
        card.mount('#card-element');
        console.log('Card mounted');

        const form = document.getElementById('payment-form');
        console.log('Form found:', form);
    </script>
</body>
</html>
