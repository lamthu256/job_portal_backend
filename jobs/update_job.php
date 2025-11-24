<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$job_id = $data['job_id'] ?? null;
$recruiter_id = $data['recruiter_id'] ?? '';
$title = $data['title'] ?? '';
$salary = $data['salary'] ?? '';
$job_type = $data['job_type'] ?? 1;
$workplace_type = $data['workplace_type'] ?? 1;
$experience = $data['experience'] ?? '';
$vacancy_count = $data['vacancy_count'] ?? 1;
$field_id = $data['field_id'] ?? null;
$job_description = $data['job_description'] ?? '';
$requirements = $data['requirements'] ?? '';
$interest = $data['interest'] ?? '';
$work_location = $data['work_location'] ?? '';
$working_time = $data['working_time'] ?? '';
$deadline = $data['deadline'] ?? date("Y-m-d");
$job_status = $data['job_status'] ?? 'Open';

if (!$job_id || !$recruiter_id) {
    echo json_encode(['success' => false, 'message' => 'Missing job_id or recruiter_id']);
    exit;
}

$stmt = $conn->prepare("
  UPDATE jobs SET
    title = ?,
    salary = ?,
    job_type = ?,
    workplace_type = ?,
    experience = ?,
    vacancy_count = ?,
    field_id = ?,
    job_description = ?,
    requirements = ?,
    interest = ?,
    work_location = ?,
    working_time = ?,
    deadline = ?,
    job_status = ?
  WHERE id = ? AND recruiter_id = ?
");

$stmt->bind_param(
    "sssssiisssssssii",
    $title,
    $salary,
    $job_type,
    $workplace_type,
    $experience,
    $vacancy_count,
    $field_id,
    $job_description,
    $requirements,
    $interest,
    $work_location,
    $working_time,
    $deadline,
    $job_status,
    $job_id,
    $recruiter_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Job updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update job']);
}
