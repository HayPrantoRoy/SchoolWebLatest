<?php
session_start();

// Store user name for goodbye message (optional)
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear any additional cookies that might have been set
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Prevent caching of this page
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Set success message for login page
session_start(); // Start new session for the message
$_SESSION['logout_message'] = 'You have been successfully logged out.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - Amar Campus</title>
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

        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
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

        .logout-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .logout-title {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
        }

        .logout-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .redirect-info {
            color: #999;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .manual-link {
            display: inline-block;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .manual-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .goodbye-message {
            background: rgba(102, 126, 234, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: #667eea;
            font-weight: 500;
        }

        /* Success Animation */
        .success-check {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #28a745;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: checkBounce 0.6s ease-out;
        }

        .success-check::after {
            content: '✓';
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        @keyframes checkBounce {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
                opacity: 1;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Mobile Responsiveness */
        @media (max-width: 480px) {
            .logout-container {
                padding: 30px 20px;
                margin: 10px;
            }

            .logout-title {
                font-size: 1.6rem;
            }

            .logout-message {
                font-size: 1rem;
            }

            .logout-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="success-check"></div>
        
        <div class="logout-icon">👋</div>
        
        <h1 class="logout-title">Goodbye!</h1>
        
        <?php if (!empty($user_name)): ?>
            <div class="goodbye-message">
                Thank you for using Amar Campus, <?php echo htmlspecialchars($user_name); ?>!
            </div>
        <?php endif; ?>
        
        <p class="logout-message">
            You have been successfully logged out.<br>
            Your session has been securely terminated.
        </p>
        
        <div class="loading-spinner"></div>
        
        <p class="redirect-info">
            Redirecting you to login page in <span id="countdown">3</span> seconds...
        </p>
        
        <a href="index.php" class="manual-link">
            Go to Login Page Now
        </a>
    </div>

    <script>
        // Countdown timer
        let countdown = 3;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                // Redirect to login page
                window.location.href = 'index.php';
            }
        }, 1000);

        // Immediate redirect if JavaScript is disabled
        setTimeout(() => {
            if (countdown > 0) {
                window.location.href = 'index.php';
            }
        }, 3100);

        // Clear any remaining data from localStorage/sessionStorage
        if (typeof(Storage) !== "undefined") {
            localStorage.clear();
            sessionStorage.clear();
        }

        // Clear browser history to prevent back button issues
        history.replaceState(null, null, window.location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Prevent caching
        window.onbeforeunload = function() {
            // Clear any cached data
            if (window.performance && window.performance.navigation.type === 1) {
                // Page was reloaded, redirect to login
                window.location.href = 'index.php';
            }
        };
    </script>
</body>
</html>