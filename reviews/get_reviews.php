<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$job_id = $data['job_id'] ?? '';

if (!$job_id) {
    echo json_encode(['success' => false, 'message' => 'Missing job_id']);
    exit;
}

$count_sql = "SELECT COUNT(*) AS total_reviews FROM reviews r JOIN candidates c ON r.user_id = c.user_id WHERE job_id = ? AND status = 'sent'";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $job_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_reviews = ($count_row['total_reviews'] ?? 0);

$sql = "SELECT r.*, c.full_name, c.avatar_url FROM reviews r JOIN candidates c ON r.user_id = c.user_id WHERE job_id = ? ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode([
    'success' => true,
    'total_reviews' => $total_reviews,
    'reviews' => $reviews,
]);
