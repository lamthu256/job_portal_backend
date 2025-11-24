<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$job_id = $data['job_id'] ?? null;
$recruiter_id = $data['recruiter_id'] ?? null;

if (!$job_id || !$recruiter_id) {
    echo json_encode(['success' => false, 'message' => 'Missing job_id or recruiter_id']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND recruiter_id = ?");
$stmt->bind_param("ii", $job_id, $recruiter_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Job deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete job']);
}
