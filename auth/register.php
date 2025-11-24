<?php
require_once '../db_connect.php'; // ket noi DB
header('Content-Type: application/json'); // dinh dang phan hoi Json

// Nhan du lieu JSON tu Flutter
$data = json_decode(file_get_contents("php://input"), true);

// Lay du lieu
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role = $data['role'] ?? 'candidate';

// Kiem tra du lieu bat buoc
if (!$email || !$password || !$role || !$name) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

// Kiem tra email da ton tai chua
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows() > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

// Ma hoa mat khau
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Them nguoi dung vao bang users
$stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $hashed_password, $role);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;

    if ($role === 'candidate') {
        $stmt2 = $conn->prepare("INSERT INTO candidates (user_id, full_name) VALUES (?, ?)");
        $stmt2->bind_param("is", $user_id, $name);
        $stmt2->execute();
    }

    if ($role === 'recruiter') {
        $stmt2 = $conn->prepare("INSERT INTO recruiters (user_id, company_name) VALUES (?, ?)");
        $stmt2->bind_param("is", $user_id, $name);
        $stmt2->execute();
    }

    echo json_encode(['success' => true, 'message' => 'User registered']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error occurred']);
}
