<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

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

if (!$recruiter_id) {
    echo json_encode(['success' => false, 'message' => 'Missing recruiter_id']);
    exit;
}

$stmt = $conn->prepare("
  INSERT INTO jobs (
    recruiter_id, title, salary, job_type, workplace_type,
    experience, vacancy_count, field_id,
    job_description, requirements, interest,
    work_location, working_time, deadline
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isssssiissssss",
    $recruiter_id,
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
    $deadline
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Job created successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create job']);
}
