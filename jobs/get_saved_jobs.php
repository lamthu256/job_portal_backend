<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$count_sql = "SELECT COUNT(*) AS total_saved FROM job_saved WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_saved = $count_row['total_saved'] ?? 0;

$jobs_sql = "SELECT jobs.*, recruiters.company_name, recruiters.logo_url, job_fields.name AS field_name, job_saved.saved_at
             FROM job_saved
             JOIN jobs ON job_saved.job_id = jobs.id
             JOIN recruiters ON jobs.recruiter_id = recruiters.user_id
             LEFT JOIN job_fields ON jobs.field_id = job_fields.id
             WHERE job_saved.user_id = ?
             ORDER BY job_saved.saved_at DESC";

$jobs_stmt = $conn->prepare($jobs_sql);
$jobs_stmt->bind_param("i", $user_id);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();

$saved_jobs = [];
while ($row = $jobs_result->fetch_assoc()) {
    $saved_jobs[] = $row;
}

echo json_encode([
    'success' => true,
    'total_saved' => $total_saved,
    'saved_jobs' => $saved_jobs
]);
