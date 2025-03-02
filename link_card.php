<?php
session_start();

// Include necessary files before any action
require_once 'Database.php';
require_once 'vendor/autoload.php';

// Database connection and email fetch
$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$_SESSION['email'] = $user['email'];

\Stripe\Stripe::setApiKey('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle the form submission to link the card
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("Starting card linking process for user ID: " . $_SESSION['user_id']);
    
        // Log the token received
        error_log("Stripe token received: " . $_POST['stripeToken']);
        // First verify the Stripe key is set
        \Stripe\Stripe::setApiKey('sk_test_51QhYByDUpDhJwyLXGAa1rwi0BavnvBas6DFEFPFeVGUcE1b5PycvTk7vz202yLrnA4xe0WYmEjNJHT2SRmYVj2Jg00cMElEdwT');

        // Create customer with explicit token
        $customer = \Stripe\Customer::create([
            'source' => $_POST['stripeToken'],  // This should be the token from the logs
            'email' => $_SESSION['email'],
            'metadata' => [
                'user_id' => $_SESSION['user_id'],
                'timestamp' => time()
            ]
        ]);

        // Log the customer creation response
        error_log("Customer Created: " . json_encode($customer));

        // Update database with customer ID
        $stmt = $db->prepare("UPDATE users SET stripe_customer_id = ?, has_payment_method = 1 WHERE id = ?");
        $result = $stmt->execute([$customer->id, $_SESSION['user_id']]);
        if ($result) {
            // Verify the update
            $verify = $db->prepare("SELECT stripe_customer_id, has_payment_method FROM users WHERE id = ?");
            $verify->execute([$_SESSION['user_id']]);
            $updated = $verify->fetch(PDO::FETCH_ASSOC);
            error_log("Verification - Customer ID: " . $updated['stripe_customer_id'] . ", Has Payment Method: " . $updated['has_payment_method']);
        }

    } catch (\Stripe\Exception\CardException $e) {
        error_log("Stripe Card Exception: " . $e->getMessage());
        throw $e;
    } catch (\Exception $e) {
        error_log("General Exception: " . $e->getMessage());
        throw $e;
    }}
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="scan_qr.php">Scan QR</a></li>
                    <li class="nav-item"><a class="nav-link" href="generate_qr.php">Generate QR</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
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
                        <?php if (isset($error)): ?>
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
        // Initialize Stripe.js with your public key
        const stripe = Stripe('pk_test_51QhYByDUpDhJwyLXF2lYx388XY2itWsvCHxxIMs80XAAvHapt0nEp4DU3fANUji9tRYICQZpQON4xq4nANcPNKud00DbOoP1me');
        const elements = stripe.elements();
        
        // Create an instance of the card Element
        const card = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });

        // Mount the card element to the DOM
        card.mount('#card-element');

        // Handle form submission
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            
            const {token, error} = await stripe.createToken(card);
            
            if (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
                submitButton.disabled = false;
            } else {
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);
                
                // This will submit the form and trigger the PHP redirect to dashboard
                form.submit();
            }
        });
    </script>
</body>
</html>
