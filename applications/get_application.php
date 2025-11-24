<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$application_id = $data['application_id'] ?? '';

if (!$application_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$sql = "SELECT j.title, r.company_name, c.full_name, a.* FROM applications a JOIN jobs j ON a.job_id = j.id JOIN candidates c ON a.candidate_id = c.user_id JOIN recruiters r ON j.recruiter_id = r.user_id WHERE a.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

$application = [];
while ($row = $result->fetch_assoc()) {
    $application[] = $row;
}

echo json_encode([
    'success' => true,
    'application' => $application
]);
