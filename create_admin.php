<?php
require_once 'Database.php';

$database = new Database();
$db = $database->getConnection();

$email = 'admin@wantokpay.com';
$password = 'your_secure_password';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("INSERT INTO admin (email, password_hash) VALUES (?, ?)");
$stmt->execute([$email, $password_hash]);

echo "Admin account created successfully";
