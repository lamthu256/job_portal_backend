<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$review_id = $data['review_id'] ?? '';
$status = $data['status'] ?? '';

if (!$review_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$stmt = $conn->prepare("UPDATE reviews SET status=? WHERE id=?");
$stmt->bind_param(
    "si",
    $status,
    $review_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update review']);
}
