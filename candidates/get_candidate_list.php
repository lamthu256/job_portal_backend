<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$count_sql = "SELECT COUNT(DISTINCT a.candidate_id) AS total_candidate FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.recruiter_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_candidate = $count_row['total_candidate'] ?? 0;

$sql = "SELECT c.*, u.email, COUNT(a.id) AS total_application FROM applications a JOIN jobs j ON a.job_id = j.id JOIN candidates c ON a.candidate_id = c.user_id JOIN users u ON c.user_id = u.id WHERE j.recruiter_id = ? GROUP BY c.user_id;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$candidate_list = [];
while ($row = $result->fetch_assoc()) {
    $candidate_list[] = $row;
}

echo json_encode([
    'success' => true,
    'total_candidate' => $total_candidate,
    'candidate_list' => $candidate_list
]);
