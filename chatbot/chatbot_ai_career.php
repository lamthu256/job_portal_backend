<?php
require_once '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);
$user_message = $data['message'] ?? '';
$chat_history = $data['chat_history'] ?? [];

if (!$user_message) {
    echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
    exit;
}

// 1. Lấy tất cả job titles
$job_titles = getAllJobTitles($conn);

if (empty($job_titles)) {
    echo json_encode([
        'success' => true,
        'bot_message' => 'Hiện tại không có công việc nào mở. Vui lòng quay lại sau!',
        'recommendations' => []
    ]);
    exit;
}

// 2. Gọi Gemini API để phân tích kỹ năng và lọc jobs
$ai_result = callGeminiForFiltering($user_message, $job_titles);

if (!$ai_result['success']) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $ai_result['error']]);
    exit;
}

// 3. Lấy chi tiết jobs từ database
$job_titles_list = $ai_result['recommended_jobs'] ?? [];
$job_recommendations = getJobsByTitles($conn, $job_titles_list);

echo json_encode([
    'success' => true,
    'bot_message' => $ai_result['response'],
    'recommendations' => $job_recommendations,
    'detected_skills' => $ai_result['detected_skills'] ?? []
]);

// ==================== FUNCTIONS ====================

function getAllJobTitles($conn)
{
    $sql = "SELECT DISTINCT title FROM jobs WHERE job_status = 'Open' LIMIT 100";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $titles = [];
    while ($row = $result->fetch_assoc()) {
        $titles[] = $row['title'];
    }

    return $titles;
}

function callGeminiForFiltering($user_message, $job_titles)
{
    $gemini_api_key = getenv('GEMINI_API_KEY'); // ← Thay bằng key thực
    $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    $jobs_list = json_encode($job_titles, JSON_UNESCAPED_UNICODE);

    $prompt = "Bạn là AI Career Assistant.

TIN NHẮN TỪ CANDIDATE:
\"$user_message\"

DANH SÁCH JOB TITLES:
$jobs_list

HÃY:
1. Trích xuất kỹ năng từ tin nhắn
2. Lọc ra max 5 job titles phù hợp nhất
3. Trả về JSON:
{
  \"response\": \"Câu trả lời thân thiện 2-3 câu tiếng Việt\",
  \"detected_skills\": [\"skill1\", \"skill2\"],
  \"recommended_jobs\": [\"Job Title 1\", \"Job Title 2\"]
}

Lưu ý:
- Nếu không có job nào phù hợp: recommended_jobs = []
- Luôn dùng tiếng Việt
- Trả về đúng tên job từ danh sách
- CHỈ trả về JSON, không có text khác";

    $request_body = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 500,
            'topP' => 0.95,
            'topK' => 40
        ]
    ];

    $url = $endpoint . '?key=' . $gemini_api_key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        $error_data = json_decode($response, true);
        $error_msg = $error_data['error']['message'] ?? 'HTTP ' . $http_code;
        return ['success' => false, 'error' => $error_msg];
    }

    $response_data = json_decode($response, true);

    if (!isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
        return ['success' => false, 'error' => 'Invalid Gemini response'];
    }

    $ai_response = $response_data['candidates'][0]['content']['parts'][0]['text'];

    // Parse JSON
    $json_start = strpos($ai_response, '{');
    $json_end = strrpos($ai_response, '}') + 1;

    if ($json_start === false) {
        return ['success' => false, 'error' => 'Parse error: ' . substr($ai_response, 0, 100)];
    }

    $json_string = substr($ai_response, $json_start, $json_end - $json_start);
    $parsed = json_decode($json_string, true);

    if (!$parsed) {
        return ['success' => false, 'error' => 'Decode error'];
    }

    return [
        'success' => true,
        'response' => $parsed['response'] ?? 'Không có phản hồi',
        'detected_skills' => $parsed['detected_skills'] ?? [],
        'recommended_jobs' => $parsed['recommended_jobs'] ?? []
    ];
}

function getJobsByTitles($conn, $job_titles)
{
    if (empty($job_titles)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($job_titles), '?'));

    $sql = "SELECT j.id, j.title, j.salary, j.job_type, j.work_location, r.user_id, r.company_name, r.logo_url
            FROM jobs j
            LEFT JOIN recruiters r ON j.recruiter_id = r.user_id
            WHERE j.title IN ($placeholders) AND j.job_status = 'Open'
            GROUP BY j.id";

    $stmt = $conn->prepare($sql);

    $types = str_repeat('s', count($job_titles));
    $stmt->bind_param($types, ...$job_titles);

    $stmt->execute();
    $result = $stmt->get_result();

    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = [
            'job_id' => $row['id'],
            'recruiter_id' => $row['user_id'],
            'title' => $row['title'],
            'company_name' => $row['company_name'],
            'salary' => $row['salary'],
            'job_type' => $row['job_type'],
            'work_location' => $row['work_location'],
            'logo_url' => $row['logo_url']
        ];
    }

    return $jobs;
}
