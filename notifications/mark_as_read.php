<?php
// filepath: /Applications/XAMPP/xamppfiles/htdocs/Web/job_portal_api/notifications/update_notification.php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$notification_id = $data['notification_id'] ?? '';

if (!$notification_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu notification_id']);
    exit;
}

$sql = "UPDATE notifications 
        SET is_read = TRUE
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $notification_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật notification thành công',
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cập nhật notification'
    ]);
}
