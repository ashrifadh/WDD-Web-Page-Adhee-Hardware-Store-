<?php
// Start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials
$host = '127.0.0.1';
$dbname = 'adhee_hardware';
$dbusername = 'root';
$dbpassword = '';

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $dbpassword);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username']);  // Get the entered username or email
    $password = trim($_POST['password']);         // Get the entered password
    
    // Check if the username or email exists in the database (include role field)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :usernameOrEmail OR email = :usernameOrEmail");
    $stmt->bindParam(':usernameOrEmail', $usernameOrEmail); // Bind the username or email
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Check if the entered password matches the plain text password
        if ($password === $user['password']) {
            // Successful login - REGULAR CUSTOMER LOGIN
            // IMPORTANT: Clear any admin session variables to prevent admin dashboard access
            // Admin and customer logins are completely separate
            unset($_SESSION['admin_user_id']);
            unset($_SESSION['admin_username']);
            unset($_SESSION['admin_role']);
            unset($_SESSION['admin_login']);
            
            // Customer login uses regular session variables (separate from admin)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'customer';
            
            // IMPORTANT: Do NOT set admin session variables
            // Even if user has admin role in database, regular login does NOT grant admin access
            // Admin users MUST use admin_login.php to access admin dashboard
            
            // Regular customer login - ALWAYS redirect to home page
            // No admin dashboard access from regular login, regardless of role
            $_SESSION['notification'] = "Login successful! Welcome, " . htmlspecialchars($user['username']) . ".";
            
            // Force redirect to home page - no admin dashboard access
            header("Location: Index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="mk.css">
    <style>
        .notification {
            background-color: #4CAF50; /* Green */
            color: white;
            padding: 15px;
            margin: 20px 0;
            border: none;
            border-radius: 5px;
            display: none; /* Hidden by default */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Please enter your credentials to login</p>
        </div>
        
        <form action="login.php" method="post">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="input-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                <div class="forgot-password">
                    <a href="#">Forgot password?</a>
                </div>
            </div>
            
            <button type="submit" class="login-button">Log In</button>
            
            <div class="signup-link">
                Don't have an account? <a href="Register.php">Sign up</a>
            </div>
        </form>
    </div>

    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification" id="notification">
            <?php 
            echo htmlspecialchars($_SESSION['notification']); 
            unset($_SESSION['notification']); // Clear the notification after displaying
            ?>
        </div>
    <?php endif; ?>

    <script>
        // Show notification if it exists
        window.onload = function() {
            var notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'block'; // Show the notification
                setTimeout(function() {
                    notification.style.display = 'none'; // Hide after 5 seconds
                }, 5000);
            }
        };
    </script>
</body>
</html>