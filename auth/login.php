<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Account does not exits']);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Login Successful',
    'user' => [
        'id' => $user['id'],
        'role' => $user['role'],
        'email' => $email
    ]
]);
