<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once 'Database.php';
$database = new Database();
$db = $database->getConnection();

$user_id = $_POST['user_id'];

try {
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $result = $stmt->execute([$user_id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
