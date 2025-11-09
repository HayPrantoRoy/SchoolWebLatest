<?php
// GetFileList.php
session_start();
require_once "../../Admin/connection.php";

header('Content-Type: application/json');

$response = [];

/*
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
*/

$user_id = $_GET['user_id'];
$type = $_GET['type'] ?? $_POST['type'] ?? '';

if (empty($type)) {
    echo json_encode(['success' => false, 'message' => 'Type is required']);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, type, description, file_url, create_date, update_date
    FROM file_info
    WHERE user_id = ? AND type = ?
    ORDER BY create_date DESC
");

$stmt->bind_param("is", $user_id, $type);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}

$response = [
    'success' => true,
    'count' => count($files),
    'files' => $files
];

echo json_encode($response);
