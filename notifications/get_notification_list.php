<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu user_id']);
    exit;
}

$sql = "SELECT notifications.*
        FROM notifications 
        WHERE user_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode([
    'success' => true,
    'notifications' => $notifications
]);
