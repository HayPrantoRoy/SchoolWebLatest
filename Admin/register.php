<?php
session_start();
require_once 'connection.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobile_no = trim($_POST['mobile_no']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = trim($_POST['name']);
    $name_bn = trim($_POST['name_bn']);
    $address = trim($_POST['address']);
    $eiin_no = trim($_POST['eiin_no']);
    
    // Handle logo upload
    $logo_url = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['logo']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/logos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('logo_') . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                $logo_url = $upload_path;
            }
        }
    }
    
    // Validation
    if (empty($mobile_no) || empty($password) || empty($name)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/^[0-9]{11}$/', $mobile_no)) {
        $error_message = "Please enter a valid 11-digit mobile number.";
    } else {
        try {
            // Check if mobile number already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE mobile_no = ?");
            $stmt->bind_param("s", $mobile_no);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "Mobile number already exists. Please use a different number.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (mobile_no, password, name, name_bn, logo_url, address, eiin_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $mobile_no, $hashed_password, $name, $name_bn, $logo_url, $address, $eiin_no);
                
                if ($stmt->execute()) {
                    $success_message = "Registration successful! You can now login with your credentials.";
                    // Clear form data
                    $_POST = array();
                } else {
                    $error_message = "Registration failed. Please try again.";
                }
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Amar Campus</title>
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
            padding: 40px 20px;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .required {
            color: #e74c3c;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="file"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            padding: 15px 20px;
            border: 2px dashed #e1e5e9;
            border-radius: 12px;
            display: block;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-input-label:hover,
        .file-input-label.has-file {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .btn-register {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-register:active {
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

        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }

        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #27ae60; }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .register-container {
                padding: 30px 20px;
                margin: 10px;
            }

            .logo {
                font-size: 2rem;
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
    <div class="register-container">
        <div class="logo-section">
            <div class="logo">Amar Campus</div>
            <div class="subtitle">Create Your Account</div>
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

        <form method="POST" action="" enctype="multipart/form-data" id="registerForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="mobile_no">Mobile Number <span class="required">*</span></label>
                    <input type="text" 
                           id="mobile_no" 
                           name="mobile_no" 
                           placeholder="01XXXXXXXXX"
                           value="<?php echo isset($_POST['mobile_no']) ? htmlspecialchars($_POST['mobile_no']) : ''; ?>"
                           required
                           maxlength="11">
                </div>

                <div class="form-group">
                    <label for="eiin_no">EIIN Number</label>
                    <input type="text" 
                           id="eiin_no" 
                           name="eiin_no" 
                           placeholder="Enter EIIN number"
                           value="<?php echo isset($_POST['eiin_no']) ? htmlspecialchars($_POST['eiin_no']) : ''; ?>">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Institution Name (English) <span class="required">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           placeholder="Enter institution name"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="name_bn">Institution Name (Bengali)</label>
                    <input type="text" 
                           id="name_bn" 
                           name="name_bn" 
                           placeholder="প্রতিষ্ঠানের নাম"
                           value="<?php echo isset($_POST['name_bn']) ? htmlspecialchars($_POST['name_bn']) : ''; ?>">
                </div>
            </div>

            <div class="form-group full-width">
                <label for="address">Address</label>
                <textarea id="address" 
                          name="address" 
                          placeholder="Enter full address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="logo">Institution Logo</label>
                <div class="file-input-wrapper">
                    <input type="file" 
                           id="logo" 
                           name="logo" 
                           accept="image/*">
                    <label for="logo" class="file-input-label" id="logoLabel">
                        📷 Choose logo file (JPG, PNG, GIF)
                    </label>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Create password"
                           required
                           minlength="6">
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Confirm password"
                           required
                           minlength="6">
                    <div class="password-match" id="passwordMatch"></div>
                </div>
            </div>

            <button type="submit" class="btn-register" id="registerBtn">
                <div class="loading" id="loading"></div>
                <span id="btnText">Create Account</span>
            </button>
        </form>

        <div class="divider">
            <span>Already have an account?</span>
        </div>

        <div class="links">
            <a href="index.php">Login to Dashboard</a>
        </div>

        <div class="back-to-home">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>

    <script>
        // Form submission with loading animation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            const loading = document.getElementById('loading');
            const btnText = document.getElementById('btnText');
            const registerBtn = document.getElementById('registerBtn');
            
            loading.style.display = 'inline-block';
            btnText.textContent = 'Creating Account...';
            registerBtn.disabled = true;
        });

        // Mobile number formatting
        document.getElementById('mobile_no').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            e.target.value = value;
        });

        // File input handling
        document.getElementById('logo').addEventListener('change', function(e) {
            const label = document.getElementById('logoLabel');
            const file = e.target.files[0];
            
            if (file) {
                label.textContent = `📷 Selected: ${file.name}`;
                label.classList.add('has-file');
            } else {
                label.textContent = '📷 Choose logo file (JPG, PNG, GIF)';
                label.classList.remove('has-file');
            }
        });

        // Password strength checker
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                    strengthDiv.textContent = 'Weak password';
                    strengthDiv.className = 'password-strength strength-weak';
                    break;
                case 2:
                case 3:
                    strengthDiv.textContent = 'Medium strength';
                    strengthDiv.className = 'password-strength strength-medium';
                    break;
                case 4:
                case 5:
                    strengthDiv.textContent = 'Strong password';
                    strengthDiv.className = 'password-strength strength-strong';
                    break;
            }
        });

        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = e.target.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.textContent = '✓ Passwords match';
                matchDiv.className = 'password-match strength-strong';
            } else {
                matchDiv.textContent = '✗ Passwords do not match';
                matchDiv.className = 'password-match strength-weak';
            }
        });

        // Auto-focus on first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('mobile_no').focus();
        });

        // Form validation on submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const mobileNo = document.getElementById('mobile_no').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const name = document.getElementById('name').value;
            
            // Mobile number validation
            if (!/^[0-9]{11}$/.test(mobileNo)) {
                e.preventDefault();
                alert('Please enter a valid 11-digit mobile number.');
                document.getElementById('mobile_no').focus();
                return;
            }
            
            // Password validation
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                document.getElementById('password').focus();
                return;
            }
            
            // Password match validation
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                document.getElementById('confirm_password').focus();
                return;
            }
            
            // Required fields validation
            if (!name.trim()) {
                e.preventDefault();
                alert('Institution name is required.');
                document.getElementById('name').focus();
                return;
            }
        });

        // Real-time mobile number validation
        document.getElementById('mobile_no').addEventListener('blur', function(e) {
            const value = e.target.value;
            if (value && !/^[0-9]{11}$/.test(value)) {
                e.target.style.borderColor = '#e74c3c';
                e.target.style.boxShadow = '0 0 0 4px rgba(231, 76, 60, 0.1)';
            } else {
                e.target.style.borderColor = '#e1e5e9';
                e.target.style.boxShadow = 'none';
            }
        });

        // Enhanced file upload feedback
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = document.getElementById('logoLabel');
            
            if (file) {
                // Check file size (limit to 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    e.target.value = '';
                    label.textContent = '📷 Choose logo file (JPG, PNG, GIF)';
                    label.classList.remove('has-file');
                    return;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF)');
                    e.target.value = '';
                    label.textContent = '📷 Choose logo file (JPG, PNG, GIF)';
                    label.classList.remove('has-file');
                    return;
                }
                
                label.textContent = `📷 Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                label.classList.add('has-file');
            }
        });

        // Prevent form submission on Enter key (except in textarea)
        document.getElementById('registerForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });

        // Auto-resize textarea
        document.getElementById('address').addEventListener('input', function(e) {
            e.target.style.height = 'auto';
            e.target.style.height = e.target.scrollHeight + 'px';
        });
    </script>
</body>
</html>