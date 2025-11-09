<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../../Admin/connection.php';

// Check if user is logged in
/*
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}
*/

// Get the type parameter from the request
$type = isset($_GET['type']) ? $_GET['type'] : null;

// Prepare the SQL query
$sql = "SELECT * FROM website_info WHERE user_id = ?";
$params = [$_GET['user_id']];
$types = "i"; // integer for user_id

// Add type filter if provided
if ($type) {
    $sql .= " AND type = ?";
    $params[] = $type;
    $types .= "s"; // string for type
}

// Prepare and execute the statement
$stmt = $conn->prepare($sql);

// Bind parameters dynamically
if ($params) {
    $stmt->bind_param($types, ...$params);
}

// Execute query
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

// Get results
$result = $stmt->get_result();
$data = [];

// Fetch all rows
while ($row = $result->fetch_assoc()) {
    // Process image URL to make it complete if needed
    if (!empty($row['image_url']) && strpos($row['image_url'], 'http') !== 0) {
        $row['image_url'] = '../Admin/' . ltrim($row['image_url'], '/');
    }
    $data[] = $row;
}

// Return JSON response
echo json_encode([
    'success' => true,
    'count' => count($data),
    'data' => $data
]);

// Close connections
$stmt->close();
$conn->close();
?>