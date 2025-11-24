<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$stmt = $conn->prepare("UPDATE recruiters SET company_name=?, description=?, phone=?, location=?, website=?, size=?, industry=?, logo_url=? WHERE user_id=?");
$stmt->bind_param(
    "ssssssssi",
    $data['company_name'],
    $data['description'],
    $data['phone'],
    $data['location'],
    $data['website'],
    $data['size'],
    $data['industry'],
    $data['logo_url'],
    $user_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update user info']);
}
