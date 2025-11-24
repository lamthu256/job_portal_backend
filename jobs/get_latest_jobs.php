<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$sql = "SELECT 
    j.*, 
    r.logo_url,
    ROUND(AVG(rev.rating), 1) AS avg_rating
FROM jobs j
JOIN recruiters r ON j.recruiter_id = r.user_id
LEFT JOIN reviews rev ON rev.job_id = j.id AND rev.status = 'sent'
WHERE j.job_status = 'Open'
GROUP BY j.id
ORDER BY j.created_at DESC
LIMIT 10";

$result = $conn->query($sql);

$recruiters = [];

while ($row = $result->fetch_assoc()) {
    $recruiters[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $recruiters
]);
