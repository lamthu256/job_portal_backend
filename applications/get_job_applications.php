<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$job_id = $data['job_id'] ?? '';

if (!$job_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$sql = "SELECT j.title, c.full_name, a.* FROM applications a JOIN jobs j ON a.job_id = j.id JOIN candidates c ON a.candidate_id = c.user_id WHERE j.id = ? ORDER BY a.applied_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
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
