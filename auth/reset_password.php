<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? null;
$password = $data['password'] ?? null;

if (!$user_id || !$password) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $hashed, $user_id);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
