<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? '';
$job_id = $data['job_id'] ?? '';
$rating = $data['rating'] ?? '';
$content = $data['content'] ?? '';

if (!$user_id || !$job_id || !$rating || !$content) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO reviews(user_id, job_id, rating, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $user_id, $job_id, $rating, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit your review. Please try again later.']);
}
