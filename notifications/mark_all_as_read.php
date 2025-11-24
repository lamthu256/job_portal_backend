<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu user_id']);
    exit;
}

$sql = "UPDATE notifications 
        SET is_read = TRUE 
        WHERE user_id = ? AND is_read = FALSE";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {

    echo json_encode([
        'success' => true,
        'message' => "Đánh dấu tất cả thông báo đã đọc",
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cập nhật'
    ]);
}
