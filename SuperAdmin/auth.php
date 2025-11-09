<?php
session_start();

// Simple admin authentication system
// You should implement a proper admin user system in production

function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
}

function loginAdmin($username, $password) {
    // Simple hardcoded admin credentials - replace with database check in production
    $adminCredentials = [
        'admin' => 'admin123',
        'sazzad' => 'sazzad123'
    ];
    
    if (isset($adminCredentials[$username]) && $adminCredentials[$username] === $password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    
    return false;
}

function logoutAdmin() {
    session_destroy();
    header('Location: login.php');
    exit();
}

function getAdminName() {
    return $_SESSION['admin_username'] ?? 'Admin';
}
?>