<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$companies = [];
$result = $conn->query("SELECT DISTINCT company_name FROM recruiters ORDER BY company_name ASC");
while ($row = $result->fetch_assoc()) {
    $companies[] = $row['company_name'];
}

$locations = [];
$result = $conn->query("SELECT DISTINCT TRIM(SUBSTRING_INDEX(work_location, ':', 1)) AS work_location FROM jobs WHERE work_location IS NOT NULL AND work_location != '' ORDER BY work_location ASC");
while ($row = $result->fetch_assoc()) {
    $locations[] = $row['work_location'];
}

$jobFields = [];
$result = $conn->query("SELECT * FROM job_fields ORDER BY id ASC");
while ($row = $result->fetch_assoc()) {
    $jobFields[] = $row;
}

$jobTypes = ['Full time', 'Part time', 'Contract', 'Freelance', 'Internship'];

$workplaceTypes = ['Onsite', 'Remote', 'Hybrid'];

// Edit Job
$jobStatus = ['Open', 'Closed'];

echo json_encode([
    'success' => true,
    'data' => [
        'company_names' => $companies,
        'work_locations' => $locations,
        'job_fields' => $jobFields,
        'job_types' => $jobTypes,
        'workplace_types' => $workplaceTypes,
        'job_status' => $jobStatus
    ]
]);
