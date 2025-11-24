<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';
$job_id = $data['job_id'] ?? '';

if (!$user_id || !$job_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id or job_id']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM job_saved WHERE user_id = ? AND job_id = ?");
$stmt->bind_param("ii", $user_id, $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("DELETE FROM job_saved WHERE user_id = ? AND job_id = ?");
    $stmt->bind_param("ii", $user_id, $job_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'action' => 'unsaved']);
} else {
    $stmt = $conn->prepare("INSERT INTO job_saved (user_id, job_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $job_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'action' => 'saved']);
}
