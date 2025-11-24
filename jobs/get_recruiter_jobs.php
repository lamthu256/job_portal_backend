<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';
$recruiter_id = $data['recruiter_id'] ?? '';

if (!$user_id || !$recruiter_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$jobs_sql = "SELECT *, EXISTS (SELECT 1 FROM job_saved js WHERE js.user_id = ? AND js.job_id = j.id) AS isSaved FROM jobs j WHERE recruiter_id = ?";
$jobs_stmt = $conn->prepare($jobs_sql);
$jobs_stmt->bind_param("ii", $user_id, $recruiter_id);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();

$job_list = [];
while ($row = $jobs_result->fetch_assoc()) {
    $job_list[] = $row;
}

echo json_encode([
    'success' => true,
    'job_list' => $job_list,
]);
