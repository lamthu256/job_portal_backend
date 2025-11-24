<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$application_id = $data['application_id'] ?? '';
$status = $data['status'] ?? '';
$title = $data['title'] ?? '';
$companyName = $data['company_name'] ?? '';

if (!$application_id || !$status || !$title || !$companyName) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$field = strtolower($status) . '_at';
$stmt = $conn->prepare("UPDATE applications SET status = ?, $field = NOW() WHERE id = ?");
$stmt->bind_param(
    "si",
    $status,
    $application_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update application']);
}
