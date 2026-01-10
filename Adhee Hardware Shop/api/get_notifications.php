<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['notifications' => [], 'unread_count' => 0]);
    exit();
}

try {
    // Check if notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'")->fetchAll();
    if (count($tableCheck) === 0) {
        echo json_encode(['notifications' => [], 'unread_count' => 0]);
        exit();
    }
    
    // Get notifications for logged in user
    $stmt = $conn->prepare("SELECT id, order_id, message, type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unread count
    $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $countStmt->execute([$_SESSION['user_id']]);
    $unreadCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'notifications' => $notifications,
        'unread_count' => (int)$unreadCount
    ]);
} catch(PDOException $e) {
    echo json_encode(['notifications' => [], 'unread_count' => 0, 'error' => $e->getMessage()]);
}
?>

