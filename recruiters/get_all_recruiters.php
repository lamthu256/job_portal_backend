<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$sql = "SELECT * FROM recruiters ORDER BY company_name";
$result = $conn->query($sql);

$recruiters = [];

while ($row = $result->fetch_assoc()) {
    $recruiters[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $recruiters
]);
