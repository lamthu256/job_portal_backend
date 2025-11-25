<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';
$message = $data['message'] ?? [];

if (!$user_id || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit;
}

$message_json = json_encode($message, JSON_UNESCAPED_UNICODE);

date_default_timezone_set('Asia/Ho_Chi_Minh');
$time = date('Y-m-d H:i:s');

$sql = "INSERT INTO notifications (user_id, message, is_read, created_at) 
        VALUES (?, ?, FALSE, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $message_json, $time);

if ($stmt->execute()) {
    $notification_id = $stmt->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Thêm notification thành công',
        'notification_id' => $notification_id,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi thêm notification: ' . $stmt->error
    ]);
}
