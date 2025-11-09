<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amar Campus</title>
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
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-top: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            background: rgba(231, 76, 60, 0.1);
            display: none;
        }

        .success-message {
            color: #27ae60;
            text-align: center;
            margin-top: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            background: rgba(39, 174, 96, 0.1);
            display: none;
        }

        .demo-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }

        .demo-info h3 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .demo-info p {
            color: #666;
            font-size: 0.8rem;
            margin: 0.2rem 0;
        }

        /* Home page styles (hidden initially) */
        .home-page {
            display: none;
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .home-page h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        .home-page p {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .logout-btn {
            padding: 0.75rem 2rem;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="login-container" id="loginContainer">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Please sign in to your account</p>
        </div>

        

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <div class="error-message" id="errorMessage">
            Invalid username or password. Please try again.
        </div>

        <div class="success-message" id="successMessage">
            Login successful! Redirecting...
        </div>
    </div>

    <div class="home-page" id="homePage">
        <h1>Welcome Home, Sazzad!</h1>
        <p>You have successfully logged in to the system.</p>
        <p>This simulates the home.php page you would be redirected to.</p>
        <button class="logout-btn" onclick="logout()">Logout</button>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const loginContainer = document.getElementById('loginContainer');
        const homePage = document.getElementById('homePage');

        // Fixed credentials
        const VALID_USERNAME = 'Sazzad';
        const VALID_PASSWORD = '1234';

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Hide any previous messages
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
            
            // Check credentials
            if (username === VALID_USERNAME && password === VALID_PASSWORD) {
                // Show success message
                successMessage.style.display = 'block';
                
                // Simulate redirect to home.php after 1.5 seconds
                setTimeout(() => {
                    loginContainer.style.display = 'none';
                    homePage.style.display = 'block';
                    
                    // In a real application, you would redirect like this:
                    window.location.href = 'home.php';
                }, 0);
            } else {
                // Show error message
                errorMessage.style.display = 'block';
                
                // Clear the form fields
                document.getElementById('username').value = '';
                document.getElementById('password').value = '';
            }
        });

        // Logout function to return to login page
        function logout() {
            homePage.style.display = 'none';
            loginContainer.style.display = 'block';
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
            
            // Clear form fields
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
        }

        // Pre-fill demo credentials for easier testing (optional)
        //document.getElementById('username').value = 'Sazzad';
        //document.getElementById('password').value = '1234';
    </script>
</body>
</html>