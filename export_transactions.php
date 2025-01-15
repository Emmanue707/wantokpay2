<?php
session_start();
require_once 'Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("
    SELECT 
        t.created_at,
        t.type,
        t.amount,
        t.status,
        u1.username as sender_name,
        u2.username as receiver_name,
        qr.description
    FROM transactions t
    LEFT JOIN users u1 ON t.sender_id = u1.id
    LEFT JOIN users u2 ON t.receiver_id = u2.id
    LEFT JOIN qr_codes qr ON t.type = 'qr_payment' AND t.receiver_id = qr.merchant_id
    WHERE t.sender_id = ? OR t.receiver_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="transactions.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Type', 'Amount', 'Status', 'From/To', 'Description']);

foreach ($transactions as $transaction) {
    fputcsv($output, [
        $transaction['created_at'],
        $transaction['type'],
        $transaction['amount'],
        $transaction['status'],
        ($transaction['sender_id'] == $_SESSION['user_id']) ? 
            "To: {$transaction['receiver_name']}" : "From: {$transaction['sender_name']}",
        $transaction['description']
    ]);
}
