<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$stmt = $conn->prepare("UPDATE candidates SET full_name=?, phone=?, location=?, job_title=?, summary=?, experience=?, avatar_url=? WHERE user_id=?");
$stmt->bind_param(
    "sssssssi",
    $data['full_name'],
    $data['phone'],
    $data['location'],
    $data['job_title'],
    $data['summary'],
    $data['experience'],
    $data['avatar_url'],
    $user_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update user info']);
}
