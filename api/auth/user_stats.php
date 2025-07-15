<?php
require_once 'db.php';
require_once 'session_check.php';

// Check session
$session = checkSession();
$user_id = $session['user_id'];

header('Content-Type: application/json');

try {
    // Get user statistics
    $stats = [];
    
    // Total users
    $totalQuery = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $totalQuery->fetch()['total'];
    
    // Users registered today
    $todayQuery = $pdo->query("SELECT COUNT(*) as today FROM users WHERE DATE(created_at) = CURDATE()");
    $stats['today_users'] = $todayQuery->fetch()['today'];
    
    // Users registered this week
    $weekQuery = $pdo->query("SELECT COUNT(*) as week FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['week_users'] = $weekQuery->fetch()['week'];
    
    // Current user info
    $userQuery = $pdo->prepare("SELECT email, created_at FROM users WHERE id = ?");
    $userQuery->execute([$user_id]);
    $userInfo = $userQuery->fetch();
    
    $stats['current_user'] = [
        'id' => $user_id,
        'email' => $userInfo['email'],
        'created_at' => $userInfo['created_at']
    ];
    
    // Recent activity (if activity log exists)
    $activityQuery = $pdo->prepare("SELECT action, ip_address, created_at FROM activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $activityQuery->execute([$user_id]);
    $stats['recent_activity'] = $activityQuery->fetchAll();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (PDOException $e) {
    error_log("User stats error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching user statistics'
    ]);
}
?>