<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

// Get Inputs
$job_id = $_POST['job_id'] ?? '';
$candidate_id = $_POST['candidate_id'] ?? '';
$file = $_FILES['resume'] ?? null;
$introduction = $_POST['introduction'] ?? '';

if (!$job_id || !$candidate_id || !$file) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

// Firebase Storage config
$firebase_project_id = getenv('FIREBASE_PROJECT_ID'); // ← Thay bằng project ID thực
$firebase_api_key = getenv('FIREBASE_API_KEY'); // ← Thay bằng key thực
$firebase_upload_url = "https://firebasestorage.googleapis.com/v0/b/$firebase_project_id.appspot.com/o";

// Prepare filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "resume/resume_" . $candidate_id . "_" . time() . "." . $ext;

// Upload to Firebase Storage
$file_data = file_get_contents($file['tmp_name']);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $firebase_upload_url . "?name=" . urlencode($filename));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $file_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/octet-stream",
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (!isset($result['name'])) {
    echo json_encode(['success' => false, 'message' => 'Firebase upload failed', 'response' => $result]);
    exit;
}

// Public resume URL
$resume_url = "https://firebasestorage.googleapis.com/v0/b/$firebase_project_id.appspot.com/o/"
    . urlencode($filename)
    . "?alt=media";

// Insert into DB
try {
    $stmt = $conn->prepare("
        INSERT INTO applications (job_id, candidate_id, resume_url, introduction) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $job_id, $candidate_id, $resume_url, $introduction);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully',
        'resume_url' => $resume_url
    ]);
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        echo json_encode(['success' => false, 'message' => 'You have already applied for this job']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
