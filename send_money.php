<?php
session_start();
require_once 'Database.php';
require_once 'User.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['recipient_email']) || !isset($_POST['amount'])) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get recipient ID
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$_POST['recipient_email']]);
$recipient = $stmt->fetch(PDO::FETCH_ASSOC);

if ($recipient) {
    $user = new User($db);
    $user->id = $_SESSION['user_id'];
    
    if ($user->transfer($recipient['id'], $_POST['amount'])) {
        $_SESSION['success'] = "Money sent successfully!";
    } else {
        $_SESSION['error'] = "Transfer failed. Please check your balance.";
    }
}

header("Location: dashboard.php");
exit();
