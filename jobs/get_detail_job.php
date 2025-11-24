<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';
$job_id = $data['job_id'] ?? '';

if (!$user_id || !$job_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$info_sql = "SELECT 
    j.*, 
    r.*, 
    EXISTS (
        SELECT 1 
        FROM job_saved 
        WHERE job_saved.user_id = ? AND job_saved.job_id = j.id
    ) AS isSaved,
    COUNT(DISTINCT a.id) AS total_applicants,
    COUNT(DISTINCT CASE WHEN a.interviewing_at IS NOT NULL THEN a.id END) AS interviewing_count
FROM jobs j
JOIN recruiters r 
    ON j.recruiter_id = r.user_id
LEFT JOIN applications a 
    ON a.job_id = j.id
WHERE j.id = ?";

$info_stmt = $conn->prepare($info_sql);
$info_stmt->bind_param("ii", $user_id, $job_id);
$info_stmt->execute();
$info_result = $info_stmt->get_result();
$info = $info_result->fetch_assoc();

echo json_encode([
    'success' => true,
    'info' => $info,
]);
