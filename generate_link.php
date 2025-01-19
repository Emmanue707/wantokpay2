<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Payment - WANTOK PAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5><i class="bi bi-link-45deg"></i> Request Payment</h5>
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
        document.getElementById('requestType').addEventListener('change', function() {
            const userField = document.getElementById('userField');
            userField.style.display = this.value === 'specific' ? 'block' : 'none';
        });

        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('process_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const resultArea = document.getElementById('resultArea');
                    const linkResult = document.getElementById('linkResult');
                    resultArea.style.display = 'block';
                    
                    if(data.requestType === 'general') {
                        linkResult.innerHTML = `Payment Link: <strong>${data.paymentLink}</strong>`;
                        document.getElementById('copyButton').style.display = 'block';
                    } else {
                        linkResult.innerHTML = 'Payment request sent successfully!';
                        document.getElementById('copyButton').style.display = 'none';
                    }
                }
            });
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
