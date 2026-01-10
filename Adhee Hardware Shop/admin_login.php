<?php
session_start();

// If already logged in through admin login, redirect to admin dashboard
if (isset($_SESSION['admin_user_id']) && isset($_SESSION['admin_login'])) {
    header('Location: admin_dashboard.php');
    exit();
}

// If logged in through regular customer login, don't interfere
// Admin login is completely separate from customer login

$mode = $_GET['mode'] ?? 'login'; // login or signup

// Handle login
if ($_POST && $_POST['action'] === 'login') {
    require_once 'db_config.php';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        try {
            // Check if admin table exists, if not use users table (backward compatibility)
            $tables = $conn->query("SHOW TABLES LIKE 'admin'")->fetchAll();
            $adminTableExists = count($tables) > 0;
            
            if ($adminTableExists) {
                // Use separate admin table
                $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
                $stmt->execute([trim($username), trim($username)]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $isFromAdminTable = true;
            } else {
                // Admin table doesn't exist - use users table with role check (backward compatibility)
                $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
                $roleColumnExists = count($columns) > 0;
                
                if ($roleColumnExists) {
                    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
                } else {
                    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
                }
                $stmt->execute([trim($username), trim($username)]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $isFromAdminTable = false;
            }
            
            if ($user) {
                // Check password (handle both hashed and plain text passwords)
                $password_valid = false;
                $stored_password = $user['password'];
                
                // Try password_verify first (for hashed passwords) - only if password looks hashed
                if (strlen($stored_password) >= 60) {
                    // This looks like a hashed password
                    if (password_verify($password, $stored_password)) {
                        $password_valid = true;
                    }
                }
                
                // Try plain text comparison (for plain text passwords)
                if (!$password_valid && trim($password) === trim($stored_password)) {
                    $password_valid = true;
                }
                
                // Try case-insensitive comparison (for some edge cases)
                if (!$password_valid && strtolower(trim($password)) === strtolower(trim($stored_password))) {
                    $password_valid = true;
                }
                
                // Try MD5 hash comparison (if password was stored as MD5)
                if (!$password_valid && strlen($stored_password) === 32 && md5($password) === $stored_password) {
                    $password_valid = true;
                }
                
                if ($password_valid) {
                    // IMPORTANT: Only allow admin users to login through admin login page
                    if ($isFromAdminTable) {
                        // User is from admin table - grant access (all users in admin table are admins)
                        session_destroy();
                        session_start();
                        
                        $_SESSION['admin_user_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        $_SESSION['admin_role'] = 'admin';
                        $_SESSION['admin_login'] = true;
                        
                        header('Location: admin_dashboard.php');
                        exit();
                    } else {
                        // User is from users table - check if they have admin role
                        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
                        $roleColumnExists = count($columns) > 0;
                        $isAdmin = false;
                        
                        if ($roleColumnExists) {
                            $userRole = isset($user['role']) && $user['role'] !== null ? strtolower(trim($user['role'])) : '';
                            $isAdmin = ($userRole === 'admin');
                        }
                        
                        if ($isAdmin) {
                            // User has admin role - grant access
                            session_destroy();
                            session_start();
                            
                            $_SESSION['admin_user_id'] = $user['id'];
                            $_SESSION['admin_username'] = $user['username'];
                            $_SESSION['admin_role'] = 'admin';
                            $_SESSION['admin_login'] = true;
                            
                            header('Location: admin_dashboard.php');
                            exit();
                        } else {
                            // User does not have admin role (customer) - deny access
                            $error = "Invalid username or password.";
                        }
                    }
                } else {
                    // Generic error message for security (prevents username enumeration)
                    $error = "Invalid username or password.";
                }
            } else {
                // Generic error message for security (prevents username enumeration)
                $error = "Invalid username or password.";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter username and password";
    }
}

// Handle signup
if ($_POST && $_POST['action'] === 'signup') {
    require_once 'db_config.php';
    
    $username = $_POST['signup_username'] ?? '';
    $email = $_POST['signup_email'] ?? '';
    $password = $_POST['signup_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($username && $email && $password && $confirm_password) {
        if ($password !== $confirm_password) {
            $signup_error = "Passwords do not match";
        } else {
            try {
                // Check if username or email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetch()) {
                    $signup_error = "Username or email already exists";
                } else {
                    // Check if role column exists before creating user
                    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
                    $roleColumnExists = count($columns) > 0;
                    
                    // Create new user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    if ($roleColumnExists) {
                        // Set new user as admin by default during signup
                        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
                        $stmt->execute([$username, $email, $hashed_password]);
                        
                        // Auto login after signup
                        $_SESSION['user_id'] = $conn->lastInsertId();
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = 'admin';
                    } else {
                        // Role column doesn't exist - create user without role
                        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                        $stmt->execute([$username, $email, $hashed_password]);
                        
                        // Auto login after signup
                        $_SESSION['user_id'] = $conn->lastInsertId();
                        $_SESSION['username'] = $username;
                        
                        // Try to add role column and set as admin
                        try {
                            $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer'");
                            $conn->exec("UPDATE users SET role = 'admin' WHERE id = " . $_SESSION['user_id']);
                            $_SESSION['role'] = 'admin';
                        } catch(PDOException $e) {
                            // If adding role column fails, user will need to be set as admin manually
                            $signup_error = "Account created, but admin role could not be set automatically. Please contact administrator or run: UPDATE users SET role = 'admin' WHERE id = " . $_SESSION['user_id'];
                        }
                    }
                    
                    header('Location: admin_dashboard.php');
                    exit();
                }
            } catch(PDOException $e) {
                $signup_error = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $signup_error = "Please fill in all fields";
    }
}

$page_error = $_GET['error'] ?? '';
if ($page_error === 'use_admin_login') {
    $error = "Please use the Admin Login page to access the admin dashboard. Regular login is for customers only.";
} elseif ($page_error === 'database_error') {
    $error = "Database error occurred. Please check your database connection.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Adhee Hardware Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #2a3f54;
            --primary-light: #3a5068;
            --accent-yellow: #f8b739;
            --accent-light: #f8f9fa;
            --danger-color: #e74c3c;
            --success-color: #27ae60;
            --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.3);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 50%, #4a6741 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(248, 183, 57, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(248, 183, 57, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            animation: backgroundMove 20s ease-in-out infinite;
        }

        @keyframes backgroundMove {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 10;
            animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(60px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            color: white;
            padding: 48px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(248, 183, 57, 0.15) 0%, transparent 70%);
            animation: rotate 25s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-header .icon {
            font-size: 56px;
            color: var(--accent-yellow);
            margin-bottom: 24px;
            position: relative;
            z-index: 2;
            display: inline-block;
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
            position: relative;
            z-index: 2;
            letter-spacing: -0.5px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 16px;
            font-weight: 400;
            position: relative;
            z-index: 2;
        }

        .login-form {
            padding: 40px 32px;
        }

        /* Toggle Buttons */
        .toggle-buttons {
            display: flex;
            background: #f1f3f4;
            border-radius: 12px;
            padding: 6px;
            margin-bottom: 32px;
            position: relative;
        }

        .toggle-btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            color: #6c757d;
            position: relative;
            z-index: 2;
        }

        .toggle-btn.active {
            background: white;
            color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
        }

        .toggle-btn:hover:not(.active) {
            color: var(--primary-dark);
            background: rgba(255, 255, 255, 0.5);
        }

        .toggle-btn i {
            margin-right: 8px;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 16px 20px;
            padding-left: 52px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 400;
            transition: var(--transition);
            background: var(--accent-light);
            color: var(--primary-dark);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-yellow);
            background: white;
            box-shadow: 0 0 0 4px rgba(248, 183, 57, 0.1);
            transform: translateY(-2px);
        }

        .form-group input::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }

        .form-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-dark);
            font-size: 18px;
            margin-top: 14px;
            opacity: 0.7;
            transition: var(--transition);
        }

        .form-group input:focus + i,
        .form-group:hover i {
            color: var(--accent-yellow);
            opacity: 1;
        }

        /* Buttons */
        .login-btn {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--accent-yellow) 0%, #ffc233 100%);
            color: var(--primary-dark);
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(248, 183, 57, 0.4);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .signup-btn {
            background: linear-gradient(135deg, var(--success-color) 0%, #229954 100%);
            color: white;
        }

        .signup-btn:hover {
            box-shadow: 0 12px 28px rgba(39, 174, 96, 0.4);
        }

        /* Info Boxes */
        .demo-credentials, .info-box {
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            font-size: 14px;
            border: 1px solid;
            position: relative;
            overflow: hidden;
        }

        .demo-credentials {
            background: linear-gradient(135deg, #e8f4fd 0%, #f0f9ff 100%);
            border-color: #bee5eb;
        }

        .info-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-color: #bfdbfe;
        }

        .demo-credentials h4, .info-box h4 {
            color: var(--primary-dark);
            margin-bottom: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .demo-credentials h4 i, .info-box h4 i {
            margin-right: 8px;
            color: var(--accent-yellow);
        }

        .demo-credentials p, .info-box p {
            margin: 6px 0;
            color: #495057;
            line-height: 1.5;
        }

        /* Error Messages */
        .error-message {
            background: linear-gradient(135deg, var(--danger-color) 0%, #c0392b 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 500;
            animation: shake 0.6s ease-in-out;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        /* Back Link */
        .back-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e1e8ed;
        }

        .back-link a {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 8px;
        }

        .back-link a:hover {
            color: var(--accent-yellow);
            background: rgba(248, 183, 57, 0.1);
        }

        .back-link a i {
            margin-right: 8px;
        }

        /* Form Animations */
        #loginForm, #signupForm {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Password Strength Indicator */
        .password-strength {
            height: 4px;
            background: #e1e8ed;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: var(--transition);
            border-radius: 2px;
        }

        .strength-weak { background: var(--danger-color); width: 33%; }
        .strength-medium { background: var(--accent-yellow); width: 66%; }
        .strength-strong { background: var(--success-color); width: 100%; }

        /* Responsive Design */
        @media (max-width: 480px) {
            body {
                padding: 16px;
            }
            
            .login-container {
                max-width: 100%;
            }
            
            .login-header {
                padding: 40px 24px;
            }
            
            .login-form {
                padding: 32px 24px;
            }
            
            .login-header h1 {
                font-size: 28px;
            }
            
            .toggle-btn {
                padding: 12px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 id="headerTitle"><?php echo $mode === 'signup' ? 'Admin Signup' : 'Admin Login'; ?></h1>
            <p>Adhee Hardware Store</p>
        </div>
        
        <div class="login-form">
            <!-- Toggle Buttons -->
            <div class="toggle-buttons">
                <button type="button" class="toggle-btn <?php echo $mode === 'login' ? 'active' : ''; ?>" onclick="switchMode('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <button type="button" class="toggle-btn <?php echo $mode === 'signup' ? 'active' : ''; ?>" onclick="switchMode('signup')">
                    <i class="fas fa-user-plus"></i> Signup
                </button>
            </div>

            <!-- Login Form -->
            <div id="loginForm" style="display: <?php echo $mode === 'login' ? 'block' : 'none'; ?>;">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="demo-credentials">
                    <h4><i class="fas fa-info-circle"></i> Demo Login:</h4>
                    <p><strong>Username:</strong> Any registered user</p>
                    <p><strong>Password:</strong> Their password</p>
                    <p><em>Use any account from your users table</em></p>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               placeholder="Enter username or email">
                        <i class="fas fa-user"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter password">
                        <i class="fas fa-lock"></i>
                    </div>
                    
                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                    </button>
                </form>
            </div>

            <!-- Signup Form -->
            <div id="signupForm" style="display: <?php echo $mode === 'signup' ? 'block' : 'none'; ?>;">
                <?php if (isset($signup_error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($signup_error); ?>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <h4><i class="fas fa-user-plus"></i> Create Admin Account:</h4>
                    <p>Register a new account to access the admin panel</p>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="signup">
                    <div class="form-group">
                        <label for="signup_username">Username</label>
                        <input type="text" id="signup_username" name="signup_username" required 
                               value="<?php echo htmlspecialchars($_POST['signup_username'] ?? ''); ?>"
                               placeholder="Choose a username">
                        <i class="fas fa-user"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup_email">Email</label>
                        <input type="email" id="signup_email" name="signup_email" required 
                               value="<?php echo htmlspecialchars($_POST['signup_email'] ?? ''); ?>"
                               placeholder="Enter your email">
                        <i class="fas fa-envelope"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup_password">Password</label>
                        <input type="password" id="signup_password" name="signup_password" required 
                               placeholder="Create a password" oninput="checkPasswordStrength(this.value)">
                        <i class="fas fa-lock"></i>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <small id="strengthText" style="color: #6c757d; font-size: 12px; margin-top: 4px; display: block;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm your password">
                        <i class="fas fa-lock"></i>
                    </div>
                    
                    <button type="submit" class="login-btn signup-btn">
                        <i class="fas fa-user-plus"></i> Create Admin Account
                    </button>
                </form>
            </div>
            
            <div class="back-link">
                <a href="Index.php">
                    <i class="fas fa-arrow-left"></i> Back to Store
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            const mode = '<?php echo $mode; ?>';
            if (mode === 'login') {
                document.getElementById('username').focus();
            } else {
                document.getElementById('signup_username').focus();
            }
        });
        
        // Toggle between login and signup
        function switchMode(mode) {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const headerTitle = document.getElementById('headerTitle');
            const toggleBtns = document.querySelectorAll('.toggle-btn');
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('mode', mode);
            window.history.pushState({}, '', url);
            
            // Toggle forms
            if (mode === 'login') {
                loginForm.style.display = 'block';
                signupForm.style.display = 'none';
                headerTitle.textContent = 'Admin Login';
                document.getElementById('username').focus();
            } else {
                loginForm.style.display = 'none';
                signupForm.style.display = 'block';
                headerTitle.textContent = 'Admin Signup';
                document.getElementById('signup_username').focus();
            }
            
            // Update toggle buttons
            toggleBtns.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }
        
        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if (!strengthBar || !strengthText) return;
            
            let strength = 0;
            let feedback = '';
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Remove existing classes
            strengthBar.className = 'password-strength-bar';
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = 'var(--danger-color)';
            } else if (strength <= 3) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Medium strength';
                strengthText.style.color = 'var(--accent-yellow)';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = 'var(--success-color)';
            }
        }
        
        // Add loading state to buttons
        document.addEventListener('submit', function(e) {
            const btn = e.target.querySelector('button[type="submit"]');
            if (btn) {
                if (btn.classList.contains('signup-btn')) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                } else {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                }
                btn.disabled = true;
            }
        });

        // Password confirmation validation
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const password = document.getElementById('signup_password').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.style.borderColor = 'var(--danger-color)';
                this.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.1)';
            } else {
                this.style.borderColor = '#e1e8ed';
                this.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>