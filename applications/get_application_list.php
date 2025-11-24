<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$count_sql = "SELECT COUNT(*) AS total_applied FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.recruiter_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_applied = $count_row['total_applied'] ?? 0;

$sql = "SELECT j.title, c.full_name, a.* FROM applications a JOIN jobs j ON a.job_id = j.id JOIN candidates c ON a.candidate_id = c.user_id WHERE j.recruiter_id = ? ORDER BY a.applied_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$application_list = [];
while ($row = $result->fetch_assoc()) {
    $application_list[] = $row;
}

echo json_encode([
    'success' => true,
    'total_applied' => $total_applied,
    'application_list' => $application_list
]);
