<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$job_id = $input['job_id'] ?? null;
$candidate_id = $input['candidate_id'] ?? null;

if (!$job_id || !$candidate_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?");
$stmt->bind_param("ii", $job_id, $candidate_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Application already exists'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Application not found'
    ]);
}
