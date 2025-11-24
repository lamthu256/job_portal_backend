<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'user_id' => $row['id'],
        'message' => 'Account verified.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'This email is not registered.'
    ]);
}
