<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$candidate_id = $input['candidate_id'] ?? null;

if (!$candidate_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$count_stmt = $conn->prepare("SELECT COUNT(*) AS total_applied FROM applications WHERE candidate_id = ?");
$count_stmt->bind_param("i", $candidate_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_applied = $count_row['total_applied'] ?? 0;

$stmt = $conn->prepare("SELECT a.*, j.recruiter_id, j.title, j.salary, j.job_type, j.work_location, r.company_name, r.logo_url FROM applications a JOIN jobs j ON a.job_id = j.id JOIN recruiters r ON j.recruiter_id = r.user_id WHERE a.candidate_id = ? ORDER BY a.applied_at DESC");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();

$applied_jobs = [];
while ($row = $result->fetch_assoc()) {
    $applied_jobs[] = $row;
}

echo json_encode([
    'success' => true,
    'total_applied' => $total_applied,
    'applied_jobs' => $applied_jobs,
]);
