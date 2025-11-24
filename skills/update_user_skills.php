<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? '';
$skills = $data['skill_list'] ?? [];

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

if (!is_array($skills)) {
    echo json_encode(['success' => false, 'message' => 'Invalid skill_list']);
    exit;
}

try {
    // Xóa kỹ năng cũ
    $stmt = $conn->prepare("DELETE FROM user_skills WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    foreach ($skills as $skillName) {
        $skillName = trim($skillName);
        if ($skillName === '') continue;

        // Kiểm tra skill đã tồn tại chưa
        $stmt = $conn->prepare("SELECT id FROM skills WHERE name = ?");
        $stmt->bind_param("s", $skillName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $skill_id = $result->fetch_assoc()['id'];
        } else {
            // Nếu chưa tồn tại, thêm mới
            $stmt = $conn->prepare("INSERT INTO skills (name) VALUES (?)");
            $stmt->bind_param("s", $skillName);
            $stmt->execute();
            $skill_id = $stmt->insert_id;
        }

        // Gán user với skill
        $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $skill_id);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Skills updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
