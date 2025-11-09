<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_name_bn = $_SESSION['user_name_bn'];
$user_mobile = $_SESSION['user_mobile'];
$user_logo = $_SESSION['user_logo'];

// File upload handling
function handleFileUpload($fieldName, $targetDir = "uploads/") {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES[$fieldName]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Check if file was uploaded without errors
    if (!empty($_FILES[$fieldName]["name"]) && $_FILES[$fieldName]["error"] == 0) {
        // Allow certain file formats
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx');
        if (in_array(strtolower($fileType), $allowedTypes)) {
            // Upload file to server
            if (move_uploaded_file($_FILES[$fieldName]["tmp_name"], $targetFilePath)) {
                return $targetFilePath;
            }
        }
    }
    return false;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => ''];
    
    try {
        switch ($_POST['action']) {
            case 'add_website_info':
                $image_url = '';
                if (!empty($_FILES['image_file']['name'])) {
                    $image_url = handleFileUpload('image_file', 'uploads/images/');
                }
                
                $is_head       = isset($_POST['is_head_teacher']) ? intval($_POST['is_head_teacher']) : 0;
                $is_president  = isset($_POST['is_president']) ? intval($_POST['is_president']) : 0;

                $stmt = $conn->prepare("INSERT INTO website_info (user_id, type, name, mobile_no, designation, image_url, is_head_teacher, is_president, serial_no, blood_group, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssssiiiss", 
                    $user_id,
                    $_POST['type'], 
                    $_POST['name'], 
                    $_POST['mobile_no'], 
                    $_POST['designation'], 
                    $image_url, 
                    $is_head, 
                    $is_president,
                    $_POST['serial_no'],
                    $_POST['blood_group'],
                    $_POST['email']
                );
                $stmt->execute();
                $response = ['success' => true, 'message' => 'Website info added successfully'];
                break;
                
            case 'update_website_info':
                $image_url = $_POST['existing_image'] ?? '';
                if (!empty($_FILES['image_file']['name'])) {
                    $new_image = handleFileUpload('image_file', 'uploads/images/');
                    if ($new_image) {
                        $image_url = $new_image;
                        // Optionally delete old image file
                    }
                }
                
                $stmt = $conn->prepare("UPDATE website_info SET type=?, name=?, mobile_no=?, designation=?, image_url=?, is_head_teacher=?, is_president=?, serial_no=?, blood_group=?, email=? WHERE id=? AND user_id=?");
                $stmt->bind_param("sssssiiissii", 
                    $_POST['type'], 
                    $_POST['name'], 
                    $_POST['mobile_no'], 
                    $_POST['designation'], 
                    $image_url, 
                    intval($_POST['is_head_teacher'] ?? 0), 
                    intval($_POST['is_president'] ?? 0),
                    intval($_POST['serial_no'] ?? 0),
                    $_POST['blood_group'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['id'],
                    $user_id
                );
                $stmt->execute();
                $response = ['success' => true, 'message' => 'Website info updated successfully'];
                break;
                
            case 'delete_website_info':
                // Get image path first to delete file
                $stmt = $conn->prepare("SELECT image_url FROM website_info WHERE id=? AND user_id=?");
                $stmt->bind_param("ii", $_POST['id'], $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                // Delete record
                $stmt = $conn->prepare("DELETE FROM website_info WHERE id=? AND user_id=?");
                $stmt->bind_param("ii", $_POST['id'], $user_id);
                $stmt->execute();
                
                // Delete image file if exists
                if ($row && !empty($row['image_url']) && file_exists($row['image_url'])) {
                    unlink($row['image_url']);
                }
                
                $response = ['success' => true, 'message' => 'Website info deleted successfully'];
                break;
                
            case 'add_file_info':
                $file_url = '';
                if (!empty($_FILES['file_upload']['name'])) {
                    $file_url = handleFileUpload('file_upload', 'uploads/files/');
                }
                
                $stmt = $conn->prepare("INSERT INTO file_info (user_id, type, description, file_url) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $_POST['type'], $_POST['description'], $file_url);
                $stmt->execute();
                $response = ['success' => true, 'message' => 'File info added successfully'];
                break;
                
            case 'update_file_info':
                $file_url = $_POST['existing_file'] ?? '';
                if (!empty($_FILES['file_upload']['name'])) {
                    $new_file = handleFileUpload('file_upload', 'uploads/files/');
                    if ($new_file) {
                        $file_url = $new_file;
                        // Optionally delete old file
                    }
                }
                
                $stmt = $conn->prepare("UPDATE file_info SET type=?, description=?, file_url=? WHERE id=? AND user_id=?");
                $stmt->bind_param("sssii", $_POST['type'], $_POST['description'], $file_url, $_POST['id'], $user_id);
                $stmt->execute();
                $response = ['success' => true, 'message' => 'File info updated successfully'];
                break;
                
            case 'delete_file_info':
                // Get file path first to delete file
                $stmt = $conn->prepare("SELECT file_url FROM file_info WHERE id=? AND user_id=?");
                $stmt->bind_param("ii", $_POST['id'], $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                // Delete record
                $stmt = $conn->prepare("DELETE FROM file_info WHERE id=? AND user_id=?");
                $stmt->bind_param("ii", $_POST['id'], $user_id);
                $stmt->execute();
                
                // Delete file if exists
                if ($row && !empty($row['file_url']) && file_exists($row['file_url'])) {
                    unlink($row['file_url']);
                }
                
                $response = ['success' => true, 'message' => 'File info deleted successfully'];
                break;
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Operation failed: ' . $e->getMessage()];
    }
    
    echo json_encode($response);
    exit();
}

// Fetch data for display - Only show data for the logged-in user
if (isset($_GET['fetch']) && $_GET['fetch'] == 'website_info') {
    header('Content-Type: application/json');
    $stmt = $conn->prepare("SELECT * FROM website_info WHERE user_id = ? ORDER BY create_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit();
}

if (isset($_GET['fetch']) && $_GET['fetch'] == 'file_info') {
    header('Content-Type: application/json');
    $stmt = $conn->prepare("SELECT * FROM file_info WHERE user_id = ? ORDER BY create_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Amar Campus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --sidebar-width: 250px;
            --header-height: 70px;
            --transition: all 0.3s ease;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius: 12px;
            --radius-sm: 8px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--dark);
        }

        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 0;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            transition: var(--transition);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-logo {
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo i {
            font-size: 28px;
        }

        .sidebar-nav {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            margin-right: 10px;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: var(--transition);
        }

        /* Header */
        .header {
            background: white;
            height: var(--header-height);
            padding: 0 30px;
            box-shadow: var(--shadow);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            box-shadow: var(--shadow);
        }

        .user-details h3 {
            color: var(--dark);
            font-size: 16px;
            margin-bottom: 2px;
        }

        .user-details p {
            color: var(--gray);
            font-size: 14px;
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--light-gray);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .card-description {
            color: var(--gray);
            font-size: 14px;
            line-height: 1.5;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border: 1px solid var(--light-gray);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Content Sections */
        .content-section {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        .content-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header {
            background: white;
            padding: 20px 25px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--light-gray);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-title i {
            color: var(--primary);
            font-size: 1.8rem;
        }

        .action-btns {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }

        .btn-secondary {
            background: var(--gray);
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            border: 1px solid var(--light-gray);
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid var(--light-gray);
            vertical-align: middle;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: var(--info);
            color: white;
        }

        .btn-edit:hover {
            background: #3d85c6;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: var(--radius);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.4s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .close-btn:hover {
            background: var(--light-gray);
            color: var(--dark);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 500;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--light-gray);
            border-radius: var(--radius-sm);
            font-size: 14px;
            transition: var(--transition);
            background: white;
            font-family: inherit;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-group {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-upload-container {
            border: 2px dashed var(--light-gray);
            border-radius: var(--radius-sm);
            padding: 20px;
            text-align: center;
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .file-upload-container:hover {
            border-color: var(--primary);
        }

        .file-upload-container i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .file-upload-label {
            display: inline-block;
            background: var(--light);
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }

        .file-upload-label:hover {
            background: var(--light-gray);
        }

        .file-input {
            display: none;
        }

        .file-preview {
            margin-top: 15px;
            text-align: center;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--light-gray);
        }

        .file-preview a {
            display: inline-block;
            margin-top: 10px;
            color: var(--primary);
            text-decoration: none;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid var(--light-gray);
        }

        /* Badge Styles */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-authority { background: var(--info); color: white; }
        .badge-teacher { background: var(--success); color: white; }
        .badge-staff { background: var(--warning); color: white; }
        .badge-classroutine { background: #17a2b8; color: white; }
        .badge-syllabus { background: #6f42c1; color: white; }
        .badge-examroutine { background: #fd7e14; color: white; }
        .badge-result { background: var(--danger); color: white; }
        .badge-instituteinfo { background: #20c997; color: white; }
        .badge-gallery { background: #e83e8c; color: white; }
        .badge-sliderimage { background: #fd7e14; color: white; }
        .badge-notice { background: #20c997; color: white; }

        .image-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 2px solid var(--light-gray);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            animation: slideIn 0.4s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.active {
                transform: translateX(0);
                width: var(--sidebar-width);
            }
            
            .menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .header {
                padding: 0 15px;
                flex-direction: column;
                height: auto;
                padding: 15px;
                gap: 15px;
            }
            
            .dashboard-grid, .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .action-btns {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .modal-content {
                margin: 20px;
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Menu Toggle Button */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            background: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        @media (max-width: 992px) {
            .menu-toggle {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <!-- Menu Toggle Button -->
    <div class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-school"></i>
                <span>Amar Campus</span>
            </div>
        </div>
        
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="#" class="nav-link active" onclick="showDashboard()">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showWebsiteInfo()">
                    <i class="fas fa-users"></i>
                    <span>Website Info</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showFileInfo()">
                    <i class="fas fa-file"></i>
                    <span>File Info</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1 class="page-title" id="pageTitle">Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user_name); ?></h3>
                    <p><?php echo htmlspecialchars($user_mobile); ?></p>
                </div>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </div>

        <!-- Dashboard View -->
        <div id="dashboard-view" class="content-section active">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number" id="websiteInfoCount">0</span>
                    <span class="stat-label">Website Records</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number" id="fileInfoCount">0</span>
                    <span class="stat-label">File Records</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number" id="totalAuthorities">0</span>
                    <span class="stat-label">Authorities</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number" id="totalTeachers">0</span>
                    <span class="stat-label">Teachers</span>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-grid">
                <div class="dashboard-card" onclick="showWebsiteInfo()">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="card-title">Website Info</h3>
                    <p class="card-description">Manage authorities, teachers, and staff information for your website</p>
                </div>
                <div class="dashboard-card" onclick="showFileInfo()">
                    <div class="card-icon">
                        <i class="fas fa-file"></i>
                    </div>
                    <h3 class="card-title">File Info</h3>
                    <p class="card-description">Upload and manage class routines, syllabi, exam schedules, and more</p>
                </div>
            </div>
        </div>

        <!-- Website Info Section -->
        <div id="website-info-view" class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-users"></i>
                    Website Information
                </h2>
                <div class="action-btns">
                    <button class="btn btn-secondary" onclick="showDashboard()">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </button>
                    <button class="btn btn-primary" onclick="openWebsiteInfoModal()">
                        <i class="fas fa-plus"></i>
                        Add New
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Serial No</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Blood Group</th>
                            <th>Designation</th>
                            <th>Special</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="websiteInfoTable">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- File Info Section -->
        <div id="file-info-view" class="content-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-file"></i>
                    File Information
                </h2>
                <div class="action-btns">
                    <button class="btn btn-secondary" onclick="showDashboard()">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </button>
                    <button class="btn btn-primary" onclick="openFileInfoModal()">
                        <i class="fas fa-plus"></i>
                        Add New
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>File</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="fileInfoTable">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Website Info Modal -->
    <div id="websiteInfoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="websiteInfoModalTitle">Add Website Information</h3>
                <button class="close-btn" onclick="closeWebsiteInfoModal()">&times;</button>
            </div>
            
            <form id="websiteInfoForm" enctype="multipart/form-data">
                <input type="hidden" id="websiteInfoId" name="id">
                <input type="hidden" id="existingImage" name="existing_image">
                
                <div class="form-group">
                    <label class="form-label">Serial No</label>
                    <input type="number" id="websiteInfoSerial" name="serial_no" class="form-input" min="0" step="1">
                </div>

                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select id="websiteInfoType" name="type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="Authority">Authority</option>
                        <option value="Teacher">Teacher</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" id="websiteInfoName" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" id="websiteInfoMobile" name="mobile_no" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="websiteInfoEmail" name="email" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Blood Group</label>
                    <input type="text" id="websiteInfoBloodGroup" name="blood_group" class="form-input" placeholder="e.g., A+, O-, B+">
                </div>

                <div class="form-group">
                    <label class="form-label">Designation</label>
                    <input type="text" id="websiteInfoDesignation" name="designation" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Image</label>
                    <div class="file-upload-container">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag & drop or click to upload</p>
                        <label for="websiteInfoImageFile" class="file-upload-label">
                            <i class="fas fa-upload"></i> Choose File
                        </label>
                        <input type="file" id="websiteInfoImageFile" name="image_file" class="file-input" accept="image/*">
                        <div class="file-preview" id="websiteInfoImagePreview"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Special Roles</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="websiteInfoHeadTeacher" name="is_head_teacher" value="1">
                            <label for="websiteInfoHeadTeacher">Head Teacher</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="websiteInfoPresident" name="is_president" value="1">
                            <label for="websiteInfoPresident">President</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeWebsiteInfoModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="websiteInfoSubmitBtn">
                        <span class="loading" id="websiteInfoLoading" style="display: none;"></span>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- File Info Modal -->
    <div id="fileInfoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="fileInfoModalTitle">Add File Information</h3>
                <button class="close-btn" onclick="closeFileInfoModal()">&times;</button>
            </div>
            
            <form id="fileInfoForm" enctype="multipart/form-data">
                <input type="hidden" id="fileInfoId" name="id">
                <input type="hidden" id="existingFile" name="existing_file">
                
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select id="fileInfoType" name="type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="SliderImage">Slider Image</option>
                        <option value="Notice">Notice</option>
                        <option value="InstituteInfo">Institute Info</option>
                        <option value="MissionVision">Mission & Vision</option>
                        <option value="WhyStudyHere">Why Study Here</option>
                        <option value="BookList">Book List</option>
                        <option value="AcademicCalendar">Academic Calendar</option>
                        <option value="RoomCount">Room Count</option>
                        <option value="StudentCount">Student Count</option>
                        <option value="DressInfo">Dress Info</option>
                        <option value="ClassRoutine">Class Routine</option>
                        <option value="ExamRoutine">Exam Routine</option>
                        <option value="Syllabus">Syllabus</option>
                        <option value="ScienceLab">Science Lab</option>
                        <option value="ComputerLab">Computer Lab</option>
                        <option value="Library">Library</option>
                        <option value="Playground">Playground</option>
                        <option value="Result">Result</option>
                        <option value="BoardResultLink">Board Result Link</option>
                        <option value="AdmissionInfo">Admission Info</option>
                        <option value="OnlineApply">Online Apply</option>
                        <option value="AdmissionForm">Admission Form</option>
                        <option value="Gallery">Gallery</option>
                        <option value="VideoGallery">Video Gallery</option>
                        <option value="RecentNews">Recent News</option>
                        <option value="HeadTeacherWords">প্রধান শিক্ষকের বাণী</option>
                        <option value="PresidentWords">সভাপতির বাণী</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <div id="fileInfoDescriptionEditor" class="form-textarea" style="min-height:200px;"></div>
                    <textarea id="fileInfoDescription" name="description" class="form-textarea" placeholder="Enter file description..." style="display:none;"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">File</label>
                    <div class="file-upload-container">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag & drop or click to upload</p>
                        <label for="fileInfoUpload" class="file-upload-label">
                            <i class="fas fa-upload"></i> Choose File
                        </label>
                        <input type="file" id="fileInfoUpload" name="file_upload" class="file-input">
                        <div class="file-preview" id="fileInfoPreview"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeFileInfoModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="fileInfoSubmitBtn">
                        <span class="loading" id="fileInfoLoading" style="display: none;"></span>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1001;"></div>

    <script>
        // Global variables
        let websiteInfoData = [];
        let fileInfoData = [];
        let currentEditingWebsiteInfo = null;
        let currentEditingFileInfo = null;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            setupEventListeners();
            setupFileUploads();
            initQuillEditor();
        });

        // Setup event listeners
        function setupEventListeners() {
            // Website Info Form
            document.getElementById('websiteInfoForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitWebsiteInfo();
            });

            // File Info Form
            document.getElementById('fileInfoForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitFileInfo();
            });

            // Close modals on outside click
            document.getElementById('websiteInfoModal').addEventListener('click', function(e) {
                if (e.target === this) closeWebsiteInfoModal();
            });

            document.getElementById('fileInfoModal').addEventListener('click', function(e) {
                if (e.target === this) closeFileInfoModal();
            });
        }

        // Initialize Quill editor for file description (free MIT)
        let quillEditor = null;
        function initQuillEditor() {
            const editorContainer = document.getElementById('fileInfoDescriptionEditor');
            if (!editorContainer || !window.Quill) return;
            quillEditor = new Quill('#fileInfoDescriptionEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link'],
                        ['clean']
                    ]
                }
            });
        }

        // Setup file upload previews
        function setupFileUploads() {
            // Website info image upload
            const websiteImageInput = document.getElementById('websiteInfoImageFile');
            if (websiteImageInput) {
                websiteImageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.getElementById('websiteInfoImagePreview');
                            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // File info upload
            const fileInput = document.getElementById('fileInfoUpload');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const preview = document.getElementById('fileInfoPreview');
                        preview.innerHTML = `
                            <div>
                                <i class="fas fa-file" style="font-size: 3rem;"></i>
                                <p>${file.name}</p>
                                <p>(${(file.size / 1024).toFixed(2)} KB)</p>
                            </div>
                        `;
                    }
                });
            }
        }

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Navigation functions
        function showDashboard() {
            hideAllSections();
            document.getElementById('dashboard-view').classList.add('active');
            document.getElementById('pageTitle').textContent = 'Dashboard';
            updateActiveNavLink('dashboard');
            loadStats();
        }

        function showWebsiteInfo() {
            hideAllSections();
            document.getElementById('website-info-view').classList.add('active');
            document.getElementById('pageTitle').textContent = 'Website Information';
            updateActiveNavLink('website');
            loadWebsiteInfo();
        }

        function showFileInfo() {
            hideAllSections();
            document.getElementById('file-info-view').classList.add('active');
            document.getElementById('pageTitle').textContent = 'File Information';
            updateActiveNavLink('file');
            loadFileInfo();
        }

        function updateActiveNavLink(active) {
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            if (active === 'dashboard') {
                document.querySelector('.nav-link:nth-child(1)').classList.add('active');
            } else if (active === 'website') {
                //document.querySelector('.nav-link:nth-child(2)').classList.add('active');
            } else if (active === 'file') {
                //document.querySelector('.nav-link:nth-child(3)').classList.add('active');
            }
        }

        function hideAllSections() {
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
        }

        // Load statistics for dashboard
        async function loadStats() {
            try {
                const [websiteResponse, fileResponse] = await Promise.all([
                    fetch('?fetch=website_info'),
                    fetch('?fetch=file_info')
                ]);

                const websiteData = await websiteResponse.json();
                const fileData = await fileResponse.json();

                document.getElementById('websiteInfoCount').textContent = websiteData.length;
                document.getElementById('fileInfoCount').textContent = fileData.length;
                
                const authorities = websiteData.filter(item => item.type === 'Authority').length;
                const teachers = websiteData.filter(item => item.type === 'Teacher').length;
                
                document.getElementById('totalAuthorities').textContent = authorities;
                document.getElementById('totalTeachers').textContent = teachers;

                // Animate numbers
                animateNumbers();
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Animate number counters
        function animateNumbers() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                const duration = 1000;
                const step = target / (duration / 16);
                let current = 0;

                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 16);
            });
        }

        // Website Info functions
        async function loadWebsiteInfo() {
            try {
                const response = await fetch('?fetch=website_info');
                websiteInfoData = await response.json();
                renderWebsiteInfoTable();
            } catch (error) {
                showAlert('Error loading website info', 'error');
            }
        }

        function renderWebsiteInfoTable() {
            const tbody = document.getElementById('websiteInfoTable');
            
            if (websiteInfoData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 40px; color: #666;">No records found</td></tr>';
                return;
            }

            tbody.innerHTML = websiteInfoData.map(item => `
                <tr style="animation: fadeInUp 0.3s ease-out;">
                    <td>${item.serial_no ?? ''}</td>
                    <td>
                        ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}" class="image-preview" onerror="this.style.display='none'">` : '<i class="fas fa-user-circle" style="font-size: 24px;"></i>'}
                    </td>
                    <td><strong>${item.name}</strong></td>
                    <td><span class="badge badge-${item.type.toLowerCase()}">${item.type}</span></td>
                    <td>${item.mobile_no || 'N/A'}</td>
                    <td>${item.email || 'N/A'}</td>
                    <td>${item.blood_group || 'N/A'}</td>
                    <td>${item.designation || 'N/A'}</td>
                    <td>
                        ${item.is_head_teacher ? '<span class="badge" style="background: #e74c3c; color: white;">Head Teacher</span>' : ''}
                        ${item.is_president ? '<span class="badge" style="background: #f39c12; color: white;">President</span>' : ''}
                        ${!item.is_head_teacher && !item.is_president ? 'None' : ''}
                    </td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-edit" onclick="editWebsiteInfo(${item.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="deleteWebsiteInfo(${item.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function openWebsiteInfoModal(editMode = false) {
            const modal = document.getElementById('websiteInfoModal');
            const title = document.getElementById('websiteInfoModalTitle');
            
            if (editMode) {
                title.textContent = 'Edit Website Information';
            } else {
                title.textContent = 'Add Website Information';
                document.getElementById('websiteInfoForm').reset();
                document.getElementById('websiteInfoImagePreview').innerHTML = '';
                currentEditingWebsiteInfo = null;
            }
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeWebsiteInfoModal() {
            const modal = document.getElementById('websiteInfoModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            currentEditingWebsiteInfo = null;
        }

        function editWebsiteInfo(id) {
            const item = websiteInfoData.find(w => w.id == id);
            if (!item) return;

            currentEditingWebsiteInfo = id;
            
            document.getElementById('websiteInfoId').value = item.id;
            document.getElementById('websiteInfoSerial').value = item.serial_no || '';
            document.getElementById('websiteInfoType').value = item.type;
            document.getElementById('websiteInfoName').value = item.name;
            document.getElementById('websiteInfoMobile').value = item.mobile_no || '';
            document.getElementById('websiteInfoEmail').value = item.email || '';
            document.getElementById('websiteInfoBloodGroup').value = item.blood_group || '';
            document.getElementById('websiteInfoDesignation').value = item.designation || '';
            document.getElementById('existingImage').value = item.image_url || '';
            document.getElementById('websiteInfoHeadTeacher').checked = item.is_head_teacher == 1;
            document.getElementById('websiteInfoPresident').checked = item.is_president == 1;
            
            // Show current image preview
            const preview = document.getElementById('websiteInfoImagePreview');
            if (item.image_url) {
                preview.innerHTML = `<img src="${item.image_url}" alt="Current Image" onerror="this.style.display='none'">`;
            } else {
                preview.innerHTML = '';
            }
            
            openWebsiteInfoModal(true);
        }

        async function submitWebsiteInfo() {
            const form = document.getElementById('websiteInfoForm');
            const formData = new FormData(form);
            const loading = document.getElementById('websiteInfoLoading');
            const submitBtn = document.getElementById('websiteInfoSubmitBtn');
            
            // Show loading
            loading.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            // Add action
            formData.append('action', currentEditingWebsiteInfo ? 'update_website_info' : 'add_website_info');
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    closeWebsiteInfoModal();
                    loadWebsiteInfo();
                    loadStats();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Operation failed', 'error');
            } finally {
                loading.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        async function deleteWebsiteInfo(id) {
            if (!confirm('Are you sure you want to delete this record?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete_website_info');
            formData.append('id', id);
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    loadWebsiteInfo();
                    loadStats();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Delete failed', 'error');
            }
        }

        // File Info functions
        async function loadFileInfo() {
            try {
                const response = await fetch('?fetch=file_info');
                fileInfoData = await response.json();
                renderFileInfoTable();
            } catch (error) {
                showAlert('Error loading file info', 'error');
            }
        }

        function renderFileInfoTable() {
            const tbody = document.getElementById('fileInfoTable');
            
            if (fileInfoData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #666;">No records found</td></tr>';
                return;
            }

            tbody.innerHTML = fileInfoData.map(item => `
                <tr style="animation: fadeInUp 0.3s ease-out;">
                    <td><span class="badge badge-${item.type.toLowerCase()}">${item.type}</span></td>
                    <td>${item.description || 'N/A'}</td>
                    <td>
                        ${item.file_url ? 
                            `<a href="${item.file_url}" target="_blank" class="btn-edit">
                                <i class="fas fa-download"></i> Download
                            </a>` : 
                            'No file'
                        }
                    </td>
                    <td>${new Date(item.create_date).toLocaleDateString()}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-edit" onclick="editFileInfo(${item.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="deleteFileInfo(${item.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function openFileInfoModal(editMode = false) {
            const modal = document.getElementById('fileInfoModal');
            const title = document.getElementById('fileInfoModalTitle');
            
            if (editMode) {
                title.textContent = 'Edit File Information';
            } else {
                title.textContent = 'Add File Information';
                document.getElementById('fileInfoForm').reset();
                document.getElementById('fileInfoPreview').innerHTML = '';
                currentEditingFileInfo = null;
                if (quillEditor) { quillEditor.root.innerHTML = ''; }
            }
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeFileInfoModal() {
            const modal = document.getElementById('fileInfoModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            currentEditingFileInfo = null;
        }

        function editFileInfo(id) {
            const item = fileInfoData.find(f => f.id == id);
            if (!item) return;

            currentEditingFileInfo = id;
            
            document.getElementById('fileInfoId').value = item.id;
            document.getElementById('fileInfoType').value = item.type;
            if (quillEditor) {
                quillEditor.root.innerHTML = item.description || '';
            } else {
                document.getElementById('fileInfoDescription').value = item.description || '';
            }
            document.getElementById('existingFile').value = item.file_url || '';
            
            // Show current file info
            const preview = document.getElementById('fileInfoPreview');
            if (item.file_url) {
                const fileName = item.file_url.split('/').pop();
                
                preview.innerHTML = `
                    <div>
                        <i class="fas fa-file" style="font-size: 3rem;"></i>
                        <p>Current: ${fileName}</p>
                        <a href="${item.file_url}" target="_blank" style="color: #667eea; margin-left: 10px;">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                    <div style="margin-top: 10px; color: #666; font-size: 12px;">Choose new file to replace current one</div>
                `;
            } else {
                preview.innerHTML = '';
            }
            
            openFileInfoModal(true);
        }

        async function submitFileInfo() {
            const form = document.getElementById('fileInfoForm');
            if (quillEditor) {
                const html = quillEditor.root.innerHTML;
                const descEl = document.getElementById('fileInfoDescription');
                if (descEl) descEl.value = html;
            }
            const formData = new FormData(form);
            const loading = document.getElementById('fileInfoLoading');
            const submitBtn = document.getElementById('fileInfoSubmitBtn');
            
            // Show loading
            loading.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            // Add action
            formData.append('action', currentEditingFileInfo ? 'update_file_info' : 'add_file_info');
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    closeFileInfoModal();
                    loadFileInfo();
                    loadStats();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Operation failed', 'error');
            } finally {
                loading.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        async function deleteFileInfo(id) {
            if (!confirm('Are you sure you want to delete this record?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete_file_info');
            formData.append('id', id);
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    loadFileInfo();
                    loadStats();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Delete failed', 'error');
            }
        }

        // Utility functions
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            
            const alertHTML = `
                <div id="${alertId}" class="alert alert-${type}">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    ${message}
                </div>
            `;
            
            alertContainer.insertAdjacentHTML('beforeend', alertHTML);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                const alertElement = document.getElementById(alertId);
                if (alertElement) {
                    alertElement.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => alertElement.remove(), 300);
                }
            }, 3000);
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC key to close modals
            if (e.key === 'Escape') {
                closeWebsiteInfoModal();
                closeFileInfoModal();
            }
        });

        // Add CSS animation for fadeOut
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(20px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>