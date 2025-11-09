<?php
session_start();
require_once 'connection.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobile_no = trim($_POST['mobile_no']);
    $password = $_POST['password'];
    
    // Validation
    if (empty($mobile_no) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        try {
            // Check user credentials
            $stmt = $conn->prepare("SELECT id, password, name, name_bn, logo_url FROM users WHERE mobile_no = ?");
            $stmt->bind_param("s", $mobile_no);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_name_bn'] = $user['name_bn'];
                    $_SESSION['user_mobile'] = $mobile_no;
                    $_SESSION['user_logo'] = $user['logo_url'];
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid mobile number or password.";
                }
            } else {
                $error_message = "Invalid mobile number or password.";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "Login failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Amar Campus</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            animation: fadeInScale 0.6s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 5px rgba(102, 126, 234, 0.3)); }
            to { filter: drop-shadow(0 0 15px rgba(102, 126, 234, 0.6)); }
        }

        .subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border-left: 4px solid #e74c3c;
        }

        .alert-success {
            background-color: #efe;
            color: #363;
            border-left: 4px solid #27ae60;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .divider {
            margin: 20px 0;
            text-align: center;
            color: #999;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e5e9;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            font-size: 14px;
        }

        .back-to-home {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }

        .back-to-home a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-to-home a:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }

        /* Mobile Responsiveness */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }

            .logo {
                font-size: 2rem;
            }

            input[type="text"],
            input[type="password"] {
                padding: 12px 15px;
            }

            .btn-login {
                padding: 14px;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo">Amar Campus</div>
            <div class="subtitle">Admin Login Portal</div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                ⚠️ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                ✓ <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="mobile_no">Mobile Number</label>
                <input type="text" 
                       id="mobile_no" 
                       name="mobile_no" 
                       placeholder="Enter your mobile number"
                       value="<?php echo isset($_POST['mobile_no']) ? htmlspecialchars($_POST['mobile_no']) : ''; ?>"
                       required>
                <div class="input-icon">📱</div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Enter your password"
                       required>
                <div class="input-icon" onclick="togglePassword()" style="cursor: pointer;">👁️</div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <div class="loading" id="loading"></div>
                <span id="btnText">Login to Dashboard</span>
            </button>
        </form>

        <div class="divider">
            <span>New to Amar Campus?</span>
        </div>

        <div class="links">
            <a href="register.php">Create New Account</a>
        </div>

        <div class="back-to-home">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>

    <script>
        // Form submission with loading animation
        document.getElementById('loginForm').addEventListener('submit', function() {
            const loading = document.getElementById('loading');
            const btnText = document.getElementById('btnText');
            const loginBtn = document.getElementById('loginBtn');
            
            loading.style.display = 'inline-block';
            btnText.textContent = 'Logging in...';
            loginBtn.disabled = true;
        });

        // Toggle password visibility
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const icon = document.querySelector('.form-group:last-of-type .input-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                icon.textContent = '👁️';
            }
        }

        // Mobile number formatting
        document.getElementById('mobile_no').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            e.target.value = value;
        });

        // Auto-focus on first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('mobile_no').focus();
        });

        // Enter key navigation
        document.getElementById('mobile_no').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('password').focus();
            }
        });

        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>