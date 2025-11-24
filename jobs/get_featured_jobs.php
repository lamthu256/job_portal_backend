<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$user = json_decode(file_get_contents("php://input"), true);

$user_id = $user['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$sql = "SELECT 
    j.*,
    r.company_name,
    r.logo_url,
    CASE WHEN js.user_id IS NOT NULL THEN 1 ELSE 0 END AS isSaved,
    IFNULL(avg_rev.avg_rating, 0) AS avg_rating
FROM jobs j
JOIN recruiters r ON j.recruiter_id = r.user_id
LEFT JOIN job_saved js ON js.job_id = j.id AND js.user_id = ?
LEFT JOIN (
    SELECT job_id, ROUND(AVG(rating),1) AS avg_rating
    FROM reviews
    WHERE status = 'sent'
    GROUP BY job_id
) AS avg_rev ON avg_rev.job_id = j.id
WHERE j.job_status = 'Open'
ORDER BY j.created_at DESC
LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $jobs,
]);
