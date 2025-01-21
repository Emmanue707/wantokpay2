<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Payment Link - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .payment-link {
            background: #173A5E;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            word-break: break-all;
            color: #66B2FF;
            font-weight: 500;
        }
    </style>
</head><body class="dashboard-page">

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


    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5>Generate Payment Link</h5>
                    </div>
                    <div class="card-body">
                        <form id="requestForm">
                            <div class="mb-3">
                                <label>Amount (K)</label>
                                <input type="number" name="amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Description</label>
                                <input type="text" name="description" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Request Type</label>
                                <select name="requestType" class="form-control" id="requestType">
                                    <option value="general">Generate Payment Link</option>
                                    <option value="specific">Request from Specific User</option>
                                </select>
                            </div>
                            <div id="userField" class="mb-3" style="display:none;">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Generate Request</button>
                        </form>
                        
                        <div id="resultArea" class="mt-3" style="display:none;">
                            <div class="alert alert-success">
                                <div id="linkResult"></div>
                                <button id="copyButton" class="btn btn-sm btn-outline-primary mt-2">Copy Link</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const stripe = Stripe('pk_test_51QhYByDUpDhJwyLXF2lYx388XY2itWsvCHxxIMs80XAAvHapt0nEp4DU3fANUji9tRYICQZpQON4xq4nANcPNKud00DbOoP1me');

        document.getElementById('requestType').addEventListener('change', function() {
            document.getElementById('userField').style.display = 
                this.value === 'specific' ? 'block' : 'none';
        });

        document.getElementById('requestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('process_request.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if(data.success) {
                    const resultArea = document.getElementById('resultArea');
                    const linkResult = document.getElementById('linkResult');
                    resultArea.style.display = 'block';
                    
                    if(data.requestType === 'general') {
                        const fullLink = `${window.location.origin}/${data.paymentLink}`;
                        linkResult.innerHTML = `
                            <p>Payment Link Generated:</p>
                            <div class="payment-link">${fullLink}</div>
                        `;
                        document.getElementById('copyButton').style.display = 'block';
                    } else {
                        linkResult.innerHTML = '<p>Payment request sent successfully!</p>';
                        document.getElementById('copyButton').style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        document.getElementById('copyButton').addEventListener('click', function() {
            const linkText = document.getElementById('linkResult').querySelector('strong').textContent;
            navigator.clipboard.writeText(linkText);
            this.textContent = 'Copied!';
            setTimeout(() => this.textContent = 'Copy Link', 2000);
        });
    </script>
</body>
</html>
