<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit();
}

require_once 'Database.php';
$database = new Database();
$db = $database->getConnection();

$user_id = $_POST['user_id'];
$stmt = $db->prepare("UPDATE users SET is_disabled = NOT is_disabled WHERE id = ?");
$result = $stmt->execute([$user_id]);

header('Content-Type: application/json');
echo json_encode(['success' => $result]);
