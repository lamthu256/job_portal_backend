<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$keyword = $data['keyword'] ?? '';
$company = $data['company_name'] ?? '';
$location = $data['location'] ?? '';
$field_id = $data['field_id'] ?? '';
$job_type = $data['job_type'] ?? '';
$workplace_type = $data['workplace_type'] ?? '';

$sql = "SELECT jobs.*, recruiters.company_name, recruiters.logo_url, job_fields.name AS field_name,
        CASE 
            WHEN job_saved.user_id IS NOT NULL THEN 1
            ELSE 0
        END AS isSaved
        FROM jobs
        JOIN recruiters ON jobs.recruiter_id = recruiters.user_id
        LEFT JOIN job_fields ON jobs.field_id = job_fields.id
        LEFT JOIN job_saved ON job_saved.job_id = jobs.id 
        AND job_saved.user_id = ? WHERE 1";

$params = [];
$types = "";

$params[] = $user_id;
$types .= "i";

if (!empty($keyword)) {
    $sql .= " AND jobs.title LIKE ?";
    $params[] = "%$keyword%";
    $types .= "s";
}

if (!empty($company)) {
    $sql .= " AND recruiters.company_name = ?";
    $params[] = $company;
    $types .= "s";
}

if (!empty($location)) {
    $sql .= " AND TRIM(SUBSTRING_INDEX(jobs.work_location, ':', 1)) = ?";
    $params[] = $location;
    $types .= "s";
}

if (!empty($field_id)) {
    $sql .= " AND job_fields.id = ?";
    $params[] = $field_id;
    $types .= "i";
}

if (!empty($job_type)) {
    $sql .= " AND jobs.job_type = ?";
    $params[] = $job_type;
    $types .= "s";
}

if (!empty($workplace_type)) {
    $sql .= " AND jobs.workplace_type = ?";
    $params[] = $workplace_type;
    $types .= "s";
}

$sql .= " ORDER BY jobs.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $row['isSaved'] = (bool) $row['isSaved'];
    $jobs[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $jobs,
]);
