<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$user = json_decode(file_get_contents("php://input"), true);

$user_id = $user['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM recruiters WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$data = $result->fetch_assoc();

echo json_encode(['success' => true, 'data' => $data]);
