<?php
echo json_encode([
    'success' => true,
    'bot_message' => 'Chào bạn! Dựa trên kỹ năng bạn cung cấp, tôi đã tìm thấy một số vị trí công việc phù hợp. Dưới đây là những gợi ý dành cho bạn:',
    'recommendations' => [
        [
            "job_id" => 4,
            "recruiter_id" => 3,
            "title" => "PHP Web Developer",
            "company_name" => "CÔNG TY TRÁCH NHIỆM HỮU HẠN THK HOLDINGS VIỆT NAM",
            "salary" => "$1.200",
            "job_type" => "Freelance",
            "work_location" => "Hà Nội: Tòa N03T1 Ngoại Giao Đoàn, Xuân Tảo, Bắc Từ Liêm",
            "logo_url" => "https://firebasestorage.googleapis.com/v0/b/unicat-33e1c.appspot.com/o/avatar%2Fholdings.png?alt=media&token=ffb39070-264f-4717-86bf-ed9d182acbf6"
        ],
        [
            "job_id" => 19,
            "recruiter_id" => 12,
            "title" => "Java Developer (Middle)",
            "company_name" => "SHINHAN FINANCE",
            "salary" => "Thoả thuận",
            "job_type" => "Full time",
            "work_location" => "Hồ Chí Minh: Pico Plaza, 20 Cộng Hòa, Tân Bình",
            "logo_url" => "https://firebasestorage.googleapis.com/v0/b/unicat-33e1c.appspot.com/o/avatar%2Fshinhan.png?alt=media&token=a85959a6-98f0-4e67-ba19-9d58d26fa825"
        ]
    ],
    "detected_skills" => [
        "PHP",
        "JavaScript",
        "MySQL"
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
