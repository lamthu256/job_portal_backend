<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$recruiter_id = $data['recruiter_id'] ?? '';
$candidate_id = $data['candidate_id'] ?? '';

if (!$recruiter_id || !$candidate_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$sql = "SELECT j.title, c.full_name, c.avatar_url, a.* FROM applications a JOIN jobs j ON a.job_id = j.id JOIN candidates c ON a.candidate_id = c.user_id WHERE a.candidate_id = ? AND  j.recruiter_id = ? ORDER BY a.applied_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $candidate_id, $recruiter_id);
$stmt->execute();
$result = $stmt->get_result();

$application_list = [];
while ($row = $result->fetch_assoc()) {
    $application_list[] = $row;
}

echo json_encode([
    'success' => true,
    'application_list' => $application_list
]);
