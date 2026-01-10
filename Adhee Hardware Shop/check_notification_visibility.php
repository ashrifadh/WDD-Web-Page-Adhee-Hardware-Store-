<?php
/**
 * Check Notification Button Visibility
 */
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Check Notification Button</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #2a3f54; }
        .btn { display: inline-block; padding: 10px 20px; background: #f8b739; color: #2a3f54; text-decoration: none; border-radius: 5px; margin: 10px 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Check Notification Button Visibility</h1>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="success">
                ✓ You are logged in as: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong> (ID: <?php echo $_SESSION['user_id']; ?>)
            </div>
            <div class="info">
                <strong>The notification button should be visible in the header!</strong><br>
                Look for it between the search bar and cart icon.
            </div>
        <?php else: ?>
            <div class="error">
                ✗ You are NOT logged in
            </div>
            <div class="info">
                <strong>The notification button only appears when you're logged in.</strong><br>
                Please <a href="login.php" class="btn">Login</a> first to see the notification button.
            </div>
        <?php endif; ?>
        
        <h2>Where to Find the Notification Button:</h2>
        <div class="info">
            <strong>Location:</strong> In the header (top right area)<br>
            <strong>Layout:</strong> [Search Bar] → [🔔 Notifications Button] → [🛒 Cart Icon]<br>
            <strong>Appearance:</strong> Orange/yellow button with bell icon and "Notifications" text
        </div>
        
        <h2>If You Still Can't See It:</h2>
        <ol>
            <li>Make sure you're logged in: <a href="login.php" class="btn">Login</a></li>
            <li>Refresh the page (Ctrl+F5 or Cmd+Shift+R)</li>
            <li>Check browser console (F12) for errors</li>
            <li>Try a different browser</li>
            <li>Clear browser cache</li>
        </ol>
        
        <hr>
        <p>
            <a href="Index.php" class="btn">Go to Home Page</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>

