<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$user = json_decode(file_get_contents("php://input"), true);

$user_id = $user['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing used_id']);
    exit;
}

$stmt = $conn->prepare("SELECT s.id, s.name FROM user_skills us JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$skill_list = [];
while ($row = $result->fetch_assoc()) {
    $skill_list[] = $row;
}

echo json_encode(['success' => true, 'skill_list' => $skill_list]);
