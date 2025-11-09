<?php
session_start();
require_once '../Admin/connection.php';

// Check if user is logged in as admin (you can modify this based on your authentication system)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit();
// }

// Get statistics
$stats = [];

try {
    $stats = [];

    // Total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['total'] = $row['total'];
    $stmt->close();

    // Active users
    $stmt = $conn->prepare("SELECT COUNT(*) as active FROM users WHERE is_active = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['active'] = $row['active'];
    $stmt->close();

    // Inactive users
    $stmt = $conn->prepare("SELECT COUNT(*) as inactive FROM users WHERE is_active = 0");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['inactive'] = $row['inactive'];
    $stmt->close();

    // New users this month
    $stmt = $conn->prepare("SELECT COUNT(*) as new_users 
                            FROM users 
                            WHERE MONTH(create_date) = MONTH(CURRENT_DATE()) 
                              AND YEAR(create_date) = YEAR(CURRENT_DATE())");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['new_users'] = $row['new_users'];
    $stmt->close();

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'new_users' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amar Campus - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(30, 64, 175, 0.3);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .company-tagline {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-item {
            display: block;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #60a5fa;
            color: white;
        }

        .nav-item i {
            width: 20px;
            margin-right: 1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            background: #f8fafc;
        }

        /* Header */
        .header {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #e2e8f0;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Content Area */
        .content-area {
            padding: 2rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.users { background: linear-gradient(135deg, #3b82f6, #1e40af); color: white; }
        .stat-icon.active { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .stat-icon.inactive { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
        .stat-icon.new { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* User Management Section */
        .user-management {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .section-header {
            padding: 2rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .section-subtitle {
            color: #64748b;
        }

        /* Filters and Controls */
        .controls-section {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            background: #fefefe;
        }

        .controls-grid {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 1rem;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            font-size: 0.95rem;
            min-width: 150px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
        }

        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .users-table tbody tr {
            transition: all 0.2s ease;
        }

        .users-table tbody tr:hover {
            background: #f8fafc;
        }

        .user-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-active {
            background: #dcfdf7;
            color: #065f46;
        }

        .status-inactive {
            background: #fef3c7;
            color: #92400e;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .btn-edit {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-edit:hover {
            background: #bfdbfe;
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #fecaca;
        }

        .btn-toggle {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-toggle:hover {
            background: #e5e7eb;
        }

        /* Pagination */
        .pagination-container {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
            background: #fefefe;
        }

        .pagination-info {
            color: #64748b;
            font-size: 0.9rem;
        }

        .pagination {
            display: flex;
            gap: 0.25rem;
        }

        .pagination button {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            background: white;
            color: #374151;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pagination button:hover:not(:disabled) {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .pagination button.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 2000;
            backdrop-filter: blur(4px);
        }

        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
        }

        .modal-close:hover {
            background: #f1f5f9;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .form-input[type="file"] {
            padding: 0.5rem;
            border: 2px dashed #e2e8f0;
            background: #f8fafc;
        }

        .form-input[type="file"]:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn-secondary {
            background: #f8fafc;
            color: #374151;
            padding: 0.75rem 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #64748b;
        }

        .error {
            color: #dc2626;
            background: #fee2e2;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        /* Logo Preview */
        .logo-preview {
            margin-top: 0.5rem;
        }

        .logo-preview img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .controls-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .table-container {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                Amar Campus
            </div>
            <div class="company-tagline">Education Management System</div>
        </div>
        <div class="sidebar-nav">
            <a href="#" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            
            <a href="index.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1 class="header-title">Super Admin Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, Sazzad</span>
                    <div class="user-avatar">S</div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon active">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['active']; ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon inactive">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['inactive']; ?></div>
                    <div class="stat-label">Inactive Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon new">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['new_users']; ?></div>
                    <div class="stat-label">New This Month</div>
                </div>
            </div>

            <!-- User Management Section -->
            <div class="user-management">
                <div class="section-header">
                    <h2 class="section-title">User Management</h2>
                    <p class="section-subtitle">Manage and monitor all registered users in the system</p>
                </div>

                <div class="controls-section">
                    <div class="controls-grid">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="search-input" placeholder="Search users by name, mobile, or EIIN..." id="searchInput">
                        </div>
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <button class="btn-primary" onclick="openAddUserModal()" style="display:none;">
                            <i class="fas fa-plus"></i>
                            Add User
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <div class="loading" id="loadingIndicator">
                        <i class="fas fa-spinner fa-spin"></i> Loading users...
                    </div>
                    <table class="users-table" id="usersTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Mobile</th>
                                <th>Institution</th>
                                <th>EIIN No</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <!-- Users will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalRecords">0</span> results
                    </div>
                    <div class="pagination" id="pagination">
                        <!-- Pagination buttons will be generated here -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit User Modal -->
    <div class="modal-overlay" id="userModalOverlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add New User</h3>
                <button class="modal-close" onclick="closeUserModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId">
                    <div class="form-group">
                        <label class="form-label" for="userName">Full Name (English)</label>
                        <input type="text" class="form-input" id="userName" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="userNameBn">Full Name (Bengali)</label>
                        <input type="text" class="form-input" id="userNameBn">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="userMobile">Mobile Number</label>
                        <input type="tel" class="form-input" id="userMobile" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="userPassword">Password</label>
                        <input type="password" class="form-input" id="userPassword">
                        <small style="color: #64748b; font-size: 0.8rem;">Leave blank to keep current password (for edit)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="userAddress">Address</label>
                        <textarea class="form-input" id="userAddress" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="userEiin">EIIN Number</label>
                        <input type="text" class="form-input" id="userEiin">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="userLogo">Institution Logo</label>
                        <input type="file" class="form-input" id="userLogo" accept="image/*">
                        <small style="color: #64748b; font-size: 0.8rem;">Allowed: JPG, PNG, GIF, WebP (Max: 2MB)</small>
                        <div class="logo-preview" id="logoPreview"></div>
                    </div>
                    <div class="form-group">
                        <div class="form-checkbox">
                            <input type="checkbox" id="userActive" checked>
                            <label class="form-label" for="userActive">Active User</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeUserModal()">Cancel</button>
                <button type="button" class="btn-primary" onclick="saveUser()">
                    <i class="fas fa-save"></i>
                    Save User
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let itemsPerPage = 10;
        let totalRecords = 0;
        let searchTimeout;

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(loadUsers, 300);
            });
            document.getElementById('statusFilter').addEventListener('change', loadUsers);
            document.getElementById('userLogo').addEventListener('change', handleLogoPreview);
        }

        function handleLogoPreview(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('logoPreview');
            preview.innerHTML = '';
            
            if (file) {
                if (!file.type.match('image.*')) {
                    preview.innerHTML = '<div style="color: #dc2626;">Please select an image file</div>';
                    event.target.value = '';
                    return;
                }
                
                if (file.size > 2 * 1024 * 1024) {
                    preview.innerHTML = '<div style="color: #dc2626;">File size must be less than 2MB</div>';
                    event.target.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="${e.target.result}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <span style="font-size: 0.85rem; color: #64748b;">${file.name}</span>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        }

        function loadUsers() {
            const searchTerm = document.getElementById('searchInput').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            showLoading(true);
            
            const params = new URLSearchParams({
                page: currentPage,
                limit: itemsPerPage,
                search: searchTerm,
                status: statusFilter
            });
            
            fetch('api.php?' + params)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderUsers(data.users);
                        totalRecords = data.total;
                        updatePaginationInfo();
                        renderPagination();
                        document.getElementById('usersTable').style.display = 'table';
                    } else {
                        showError(data.message || 'Error loading users');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Network error occurred');
                })
                .finally(() => {
                    showLoading(false);
                });
        }

        function showLoading(show) {
            document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
            document.getElementById('usersTable').style.display = show ? 'none' : 'table';
        }

        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.textContent = message;
            document.querySelector('.table-container').appendChild(errorDiv);
            setTimeout(() => errorDiv.remove(), 5000);
        }

        function renderUsers(users) {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';
            
            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            ${user.logo_url ? 
                                `<img src="../Admin/${user.logo_url}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid #e2e8f0;">` :
                                `<div class="user-avatar-small">
                                    ${user.name ? user.name.charAt(0).toUpperCase() : 'U'}
                                </div>`
                            }
                            <div>
                                <div style="font-weight: 600;">${escapeHtml(user.name || 'No Name')}</div>
                                <div style="font-size: 0.85rem; color: #64748b;">${escapeHtml(user.name_bn || '')}</div>
                            </div>
                        </div>
                    </td>
                    <td>${escapeHtml(user.mobile_no)}</td>
                    <td>${escapeHtml(user.address || 'N/A')}</td>
                    <td>${escapeHtml(user.eiin_no || 'N/A')}</td>
                    <td>
                        <span class="status-badge ${user.is_active == 1 ? 'status-active' : 'status-inactive'}">
                            ${user.is_active == 1 ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>${formatDate(user.create_date)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon btn-edit" onclick="editUser(${user.id})" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-toggle" onclick="toggleUserStatus(${user.id})" title="Toggle Status">
                                <i class="fas fa-${user.is_active == 1 ? 'ban' : 'check'}"></i>
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteUser(${user.id})" title="Delete User" style="display:none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function updatePaginationInfo() {
            const totalPages = Math.ceil(totalRecords / itemsPerPage);
            const startIndex = totalRecords > 0 ? ((currentPage - 1) * itemsPerPage + 1) : 0;
            const endIndex = Math.min(startIndex + itemsPerPage - 1, totalRecords);
            
            document.getElementById('showingStart').textContent = startIndex;
            document.getElementById('showingEnd').textContent = endIndex;
            document.getElementById('totalRecords').textContent = totalRecords;
        }

        function renderPagination() {
            const totalPages = Math.ceil(totalRecords / itemsPerPage);
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            
            if (totalPages <= 1) return;
            
            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => goToPage(currentPage - 1);
            pagination.appendChild(prevBtn);
            
            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                const firstBtn = document.createElement('button');
                firstBtn.textContent = '1';
                firstBtn.onclick = () => goToPage(1);
                pagination.appendChild(firstBtn);
                
                if (startPage > 2) {
                    const dots = document.createElement('button');
                    dots.textContent = '...';
                    dots.disabled = true;
                    pagination.appendChild(dots);
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = i === currentPage ? 'active' : '';
                btn.onclick = () => goToPage(i);
                pagination.appendChild(btn);
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dots = document.createElement('button');
                    dots.textContent = '...';
                    dots.disabled = true;
                    pagination.appendChild(dots);
                }
                
                const lastBtn = document.createElement('button');
                lastBtn.textContent = totalPages;
                lastBtn.onclick = () => goToPage(totalPages);
                pagination.appendChild(lastBtn);
            }
            
            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => goToPage(currentPage + 1);
            pagination.appendChild(nextBtn);
        }

        function goToPage(page) {
            const totalPages = Math.ceil(totalRecords / itemsPerPage);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                loadUsers();
            }
        }

        // Modal functions
        function openAddUserModal() {
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('userActive').checked = true;
            document.getElementById('logoPreview').innerHTML = '';
            document.getElementById('userModalOverlay').style.display = 'block';
        }

        function editUser(userId) {
            fetch(`api.php?action=get&id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user) {
                        const user = data.user;
                        document.getElementById('modalTitle').textContent = 'Edit User';
                        document.getElementById('userId').value = user.id;
                        document.getElementById('userName').value = user.name || '';
                        document.getElementById('userNameBn').value = user.name_bn || '';
                        document.getElementById('userMobile').value = user.mobile_no || '';
                        document.getElementById('userAddress').value = user.address || '';
                        document.getElementById('userEiin').value = user.eiin_no || '';
                        document.getElementById('userActive').checked = user.is_active == 1;
                        document.getElementById('userPassword').value = '';
                        
                        // Handle existing logo preview
                        const preview = document.getElementById('logoPreview');
                        preview.innerHTML = '';
                        if (user.logo_url) {
                            preview.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="../Admin/${user.logo_url}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <span style="font-size: 0.85rem; color: #64748b;">Current logo</span>
                                </div>
                            `;
                        }
                        
                        document.getElementById('userModalOverlay').style.display = 'block';
                    } else {
                        showNotification('Error loading user data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Network error occurred', 'error');
                });
        }

        function closeUserModal() {
            document.getElementById('userModalOverlay').style.display = 'none';
            document.getElementById('logoPreview').innerHTML = '';
            document.getElementById('userLogo').value = '';
        }

        function saveUser() {
            const userId = document.getElementById('userId').value;
            const logoFile = document.getElementById('userLogo').files[0];
            
            const formData = new FormData();
            
            formData.append('action', userId ? 'update' : 'create');
            if (userId) formData.append('id', userId);
            formData.append('name', document.getElementById('userName').value);
            formData.append('name_bn', document.getElementById('userNameBn').value);
            formData.append('mobile_no', document.getElementById('userMobile').value);
            formData.append('address', document.getElementById('userAddress').value);
            formData.append('eiin_no', document.getElementById('userEiin').value);
            formData.append('is_active', document.getElementById('userActive').checked ? 1 : 0);
            
            // Add logo file if selected
            if (logoFile) {
                formData.append('logo', logoFile);
            }
            
            const password = document.getElementById('userPassword').value;
            if (password) {
                formData.append('password', password);
            }
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeUserModal();
                    loadUsers();
                    updateStats();
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message || 'Error saving user', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Network error occurred', 'error');
            });
        }

        function toggleUserStatus(userId) {
            if (confirm('Are you sure you want to toggle the user status?')) {
                const formData = new FormData();
                formData.append('action', 'toggle_status');
                formData.append('id', userId);
                
                fetch('api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadUsers();
                        updateStats();
                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message || 'Error updating user status', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Network error occurred', 'error');
                });
            }
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', userId);
                
                fetch('api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadUsers();
                        updateStats();
                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message || 'Error deleting user', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Network error occurred', 'error');
                });
            }
        }

        function updateStats() {
            fetch('api.php?action=stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = data.stats.total;
                        document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = data.stats.active;
                        document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = data.stats.inactive;
                        document.querySelector('.stat-card:nth-child(4) .stat-value').textContent = data.stats.new_users;
                    }
                })
                .catch(error => console.error('Error updating stats:', error));
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
                color: white;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 3000;
                font-weight: 600;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Close modal when clicking outside
        document.getElementById('userModalOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });

        // Handle form submission
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveUser();
        });

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' ? 'translateX(-100%)' : 'translateX(0px)';
        }

        // Add mobile menu button for responsive design
        if (window.innerWidth <= 768) {
            const header = document.querySelector('.header-content');
            const menuBtn = document.createElement('button');
            menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            menuBtn.style.cssText = `
                background: none;
                border: none;
                font-size: 1.2rem;
                color: #374151;
                cursor: pointer;
                padding: 0.5rem;
                margin-right: 1rem;
            `;
            menuBtn.onclick = toggleSidebar;
            header.insertBefore(menuBtn, header.firstChild);
        }
    </script>
</body>
</html>