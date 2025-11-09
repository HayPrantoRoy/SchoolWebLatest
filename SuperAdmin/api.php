<?php
session_start();
header('Content-Type: application/json');
require_once '../Admin/connection.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to send JSON response
function sendResponse($success, $message = '', $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Function to validate mobile number
function validateMobile($mobile) {
    return preg_match('/^01[3-9]\d{8}$/', $mobile);
}

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to handle file upload
function handleLogoUpload($existingLogo = '') {
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        return $existingLogo; // Return existing logo if no new file uploaded
    }
    
    $file = $_FILES['logo'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        sendResponse(false, 'Invalid file type. Allowed formats: JPG, PNG, GIF, WebP');
    }
    
    // Validate file size (2MB max)
    if ($file['size'] > 2 * 1024 * 1024) {
        sendResponse(false, 'File size must be less than 2MB');
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = '../Admin/uploads/logos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Delete old logo if it exists
        if (!empty($existingLogo) && file_exists('../Admin/' . $existingLogo)) {
            unlink('../Admin/' . $existingLogo);
        }
        return 'uploads/logos/' . $fileName;
    } else {
        sendResponse(false, 'Failed to upload logo file');
    }
}

// Function to delete logo file
function deleteLogoFile($logoPath) {
    if (!empty($logoPath) && file_exists('../Admin/' . $logoPath)) {
        unlink('../Admin/' . $logoPath);
    }
}

try {
    $action = $_REQUEST['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            handleListUsers();
            break;
        case 'get':
            handleGetUser();
            break;
        case 'create':
            handleCreateUser();
            break;
        case 'update':
            handleUpdateUser();
            break;
        case 'delete':
            handleDeleteUser();
            break;
        case 'toggle_status':
            handleToggleStatus();
            break;
        case 'stats':
            handleGetStats();
            break;
        default:
            sendResponse(false, 'Invalid action');
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage());
}

function handleListUsers() {
    global $conn;
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $whereConditions = [];
    $types = '';
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(name LIKE ? OR name_bn LIKE ? OR mobile_no LIKE ? OR eiin_no LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $types .= 'ssss';
    }
    
    if ($status !== '') {
        $whereConditions[] = "is_active = ?";
        $params[] = (int)$status;
        $types .= 'i';
    }
    
    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM users $whereClause";
    if (!empty($params)) {
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $result = $countStmt->get_result();
        $total = $result->fetch_assoc()['total'];
        $countStmt->close();
    } else {
        $result = $conn->query($countSql);
        $total = $result->fetch_assoc()['total'];
    }
    
    // Get users with pagination
    $sql = "SELECT id, mobile_no, name, name_bn, logo_url, address, eiin_no, is_active, create_date, update_date 
            FROM users 
            $whereClause 
            ORDER BY create_date DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
    
    sendResponse(true, '', ['users' => $users, 'total' => $total]);
}

function handleGetUser() {
    global $conn;
    
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        sendResponse(false, 'User ID is required');
    }
    
    $stmt = $conn->prepare("SELECT id, mobile_no, name, name_bn, logo_url, address, eiin_no, is_active, create_date, update_date FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        sendResponse(false, 'User not found');
    }
    
    sendResponse(true, '', ['user' => $user]);
}

function handleCreateUser() {
    global $conn;
    
    // Validate required fields
    $name = trim($_POST['name'] ?? '');
    $mobile_no = trim($_POST['mobile_no'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name)) {
        sendResponse(false, 'Name is required');
    }
    
    if (empty($mobile_no)) {
        sendResponse(false, 'Mobile number is required');
    }
    
    if (!validateMobile($mobile_no)) {
        sendResponse(false, 'Invalid mobile number format');
    }
    
    if (empty($password)) {
        sendResponse(false, 'Password is required');
    }
    
    if (strlen($password) < 6) {
        sendResponse(false, 'Password must be at least 6 characters long');
    }
    
    // Check if mobile number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE mobile_no = ?");
    $stmt->bind_param('s', $mobile_no);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) {
        $stmt->close();
        sendResponse(false, 'Mobile number already exists');
    }
    $stmt->close();
    
    // Check if EIIN already exists (if provided)
    $eiin_no = trim($_POST['eiin_no'] ?? '');
    if (!empty($eiin_no)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE eiin_no = ?");
        $stmt->bind_param('s', $eiin_no);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $stmt->close();
            sendResponse(false, 'EIIN number already exists');
        }
        $stmt->close();
    }
    
    // Handle logo upload
    $logo_url = handleLogoUpload();
    
    // Prepare data
    $name_bn = trim($_POST['name_bn'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $is_active = (int)($_POST['is_active'] ?? 1);
    $create_date = date('Y-m-d H:i:s');
    $update_date = $create_date;
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (mobile_no, name, name_bn, password, logo_url, address, eiin_no, is_active, create_date, update_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssssiss', $mobile_no, $name, $name_bn, hashPassword($password), $logo_url, $address, $eiin_no, $is_active, $create_date, $update_date);
    
    if ($stmt->execute()) {
        $stmt->close();
        sendResponse(true, 'User created successfully');
    } else {
        $stmt->close();
        sendResponse(false, 'Failed to create user');
    }
}

function handleUpdateUser() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        sendResponse(false, 'User ID is required');
    }
    
    // Check if user exists and get current logo
    $stmt = $conn->prepare("SELECT id, mobile_no, eiin_no, logo_url FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingUser = $result->fetch_assoc();
    $stmt->close();
    
    if (!$existingUser) {
        sendResponse(false, 'User not found');
    }
    
    // Validate required fields
    $name = trim($_POST['name'] ?? '');
    $mobile_no = trim($_POST['mobile_no'] ?? '');
    
    if (empty($name)) {
        sendResponse(false, 'Name is required');
    }
    
    if (empty($mobile_no)) {
        sendResponse(false, 'Mobile number is required');
    }
    
    if (!validateMobile($mobile_no)) {
        sendResponse(false, 'Invalid mobile number format');
    }
    
    // Check if mobile number already exists (exclude current user)
    if ($mobile_no !== $existingUser['mobile_no']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE mobile_no = ? AND id != ?");
        $stmt->bind_param('si', $mobile_no, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $stmt->close();
            sendResponse(false, 'Mobile number already exists');
        }
        $stmt->close();
    }
    
    // Check if EIIN already exists (if provided and different from current)
    $eiin_no = trim($_POST['eiin_no'] ?? '');
    if (!empty($eiin_no) && $eiin_no !== $existingUser['eiin_no']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE eiin_no = ? AND id != ?");
        $stmt->bind_param('si', $eiin_no, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $stmt->close();
            sendResponse(false, 'EIIN number already exists');
        }
        $stmt->close();
    }
    
    // Handle logo upload
    $logo_url = handleLogoUpload($existingUser['logo_url']);
    
    // Prepare data
    $name_bn = trim($_POST['name_bn'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $is_active = (int)($_POST['is_active'] ?? 1);
    $update_date = date('Y-m-d H:i:s');
    
    // Build SQL
    $sql = "UPDATE users SET 
            mobile_no = ?, 
            name = ?, 
            name_bn = ?, 
            logo_url = ?, 
            address = ?, 
            eiin_no = ?, 
            is_active = ?, 
            update_date = ?";
    
    $types = 'sssssiis';
    $params = [$mobile_no, $name, $name_bn, $logo_url, $address, $eiin_no, $is_active, $update_date];
    
    // Add password if provided
    $password = $_POST['password'] ?? '';
    if (!empty($password)) {
        if (strlen($password) < 6) {
            sendResponse(false, 'Password must be at least 6 characters long');
        }
        $sql .= ", password = ?";
        $types .= 's';
        $params[] = hashPassword($password);
    }
    
    $sql .= " WHERE id = ?";
    $types .= 'i';
    $params[] = $id;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $stmt->close();
        sendResponse(true, 'User updated successfully');
    } else {
        $stmt->close();
        sendResponse(false, 'Failed to update user');
    }
}

function handleDeleteUser() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        sendResponse(false, 'User ID is required');
    }
    
    // Get logo path before deletion
    $stmt = $conn->prepare("SELECT logo_url FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($affected > 0) {
            // Delete logo file if exists
            if (!empty($user['logo_url'])) {
                deleteLogoFile($user['logo_url']);
            }
            sendResponse(true, 'User deleted successfully');
        } else {
            sendResponse(false, 'User not found');
        }
    } else {
        $stmt->close();
        sendResponse(false, 'Failed to delete user');
    }
}

function handleToggleStatus() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        sendResponse(false, 'User ID is required');
    }
    
    // Get current status
    $stmt = $conn->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        sendResponse(false, 'User not found');
    }
    
    $newStatus = $user['is_active'] == 1 ? 0 : 1;
    $update_date = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE users SET is_active = ?, update_date = ? WHERE id = ?");
    $stmt->bind_param('isi', $newStatus, $update_date, $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $statusText = $newStatus == 1 ? 'activated' : 'deactivated';
        sendResponse(true, "User $statusText successfully");
    } else {
        $stmt->close();
        sendResponse(false, 'Failed to update user status');
    }
}

function handleGetStats() {
    global $conn;
    
    try {
        // Total users
        $result = $conn->query("SELECT COUNT(*) as total FROM users");
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Active users
        $result = $conn->query("SELECT COUNT(*) as active FROM users WHERE is_active = 1");
        $stats['active'] = $result->fetch_assoc()['active'];
        
        // Inactive users
        $result = $conn->query("SELECT COUNT(*) as inactive FROM users WHERE is_active = 0");
        $stats['inactive'] = $result->fetch_assoc()['inactive'];
        
        // New users this month
        $result = $conn->query("SELECT COUNT(*) as new_users FROM users WHERE MONTH(create_date) = MONTH(CURRENT_DATE()) AND YEAR(create_date) = YEAR(CURRENT_DATE())");
        $stats['new_users'] = $result->fetch_assoc()['new_users'];
        
        sendResponse(true, '', ['stats' => $stats]);
        
    } catch (Exception $e) {
        sendResponse(false, 'Failed to get statistics');
    }
}

// Handle preflight CORS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

// Set CORS headers for actual requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
?>