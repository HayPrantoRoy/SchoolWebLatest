<?php
session_start();
require_once "../../Admin/connection.php";

header('Content-Type: application/json');

/*
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
*/

$user_id = intval($_GET['user_id']);
$response = [];

try {
    $stmt = $conn->prepare("SELECT id, mobile_no, name, name_bn, logo_url, address, eiin_no, create_date, update_date 
                            FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $response = ['success' => true, 'user' => $result->fetch_assoc()];
    } else {
        $response = ['success' => false, 'message' => 'User not found'];
    }

    $stmt->close();
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error fetching user data'];
}

echo json_encode($response);
