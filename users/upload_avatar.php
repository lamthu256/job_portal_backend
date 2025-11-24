<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$user_id = $_POST['user_id'] ?? '';
$role = $_POST['role'] ?? '';
$file = $_FILES['avatar'] ?? null;

if (!$user_id || !$role || !$file) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

// Cho phép jpg, jpeg, png
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$allowed_ext = ['jpg', 'jpeg', 'png'];
if (!in_array(strtolower($ext), $allowed_ext)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// Đặt tên file theo role
if ($role === 'candidate') {
    $filename = "avatar/avatar_" . time() . "." . $ext;
} else if ($role === 'recruiter') {
    $filename = "avatar/logo_" . time() . "." . $ext;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

// Thông tin Firebase
$storageBucket = getenv('FIREBASE_BUCKET');   // THAY bằng bucket của bạn
$apiKey = getenv('FIREBASE_API_KEY');  // THAY bằng API KEY của bạn

// Endpoint upload
$uploadUrl = "https://firebasestorage.googleapis.com/v0/b/$storageBucket/o?uploadType=media&name=" . urlencode($filename);

// Đọc file upload
$fileData = file_get_contents($file['tmp_name']);

$headers = [
    "Content-Type: image/" . strtolower($ext),
    "Content-Length: " . strlen($fileData)
];

// Gửi file lên Firebase Storage
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uploadUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// Kiểm tra lỗi Firebase
if (isset($result['error'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Firebase upload failed',
        'firebase_error' => $result['error']
    ]);
    exit;
}

// URL xem ảnh trực tiếp
$downloadUrl = "https://firebasestorage.googleapis.com/v0/b/$storageBucket/o/" .
    urlencode($filename) . "?alt=media";

echo json_encode([
    "success" => true,
    "url" => $downloadUrl,
    "filename" => $filename
]);
