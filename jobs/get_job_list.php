<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';
$job_status = $data['job_status'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$sql = "SELECT job_status, COUNT(*) AS total FROM jobs WHERE recruiter_id = ? GROUP BY job_status";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = [];
while ($row = $result->fetch_assoc()) {
    $total[$row['job_status']] = (int)($row['total'] ?? 0);
}

$jobs_sql = "SELECT 
    j.*, 
    ROUND(AVG(r.rating), 1) AS avg_rating,
    COUNT(DISTINCT a.id) AS total_applicants,
    COUNT(DISTINCT CASE WHEN a.interviewing_at IS NOT NULL THEN a.id END) AS interviewing_count
FROM jobs j
LEFT JOIN reviews r 
    ON r.job_id = j.id AND r.status = 'sent'
LEFT JOIN applications a 
    ON a.job_id = j.id
WHERE j.recruiter_id = ?
  AND (? = '' OR j.job_status = ?)
GROUP BY j.id";

$jobs_stmt = $conn->prepare($jobs_sql);
$jobs_stmt->bind_param("iss", $user_id, $job_status, $job_status);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();

$job_list = [];
while ($row = $jobs_result->fetch_assoc()) {
    $job_list[] = $row;
}

echo json_encode([
    'success' => true,
    'total' => $total,
    'job_list' => $job_list
]);
