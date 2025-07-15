<?php
// Reusable session check function
function checkSession($redirectTo = 'login.html') {
    session_start();
    
    if (!isset($_SESSION["user_id"]) || !isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
        header("Location: $redirectTo");
        exit();
    }
    
    return [
        'user_id' => $_SESSION["user_id"],
        'email' => $_SESSION["email"],
        'logged_in' => $_SESSION["logged_in"]
    ];
}

// Check if user is admin
function isAdmin($user_id, $user_email) {
    return ($user_id == 1 || $user_email == 'admin@example.com');
}

// Log user activity
function logActivity($pdo, $user_id, $action, $ip_address) {
    try {
        // Create activity log table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $pdo->exec($createTable);
        
        // Insert activity
        $insertActivity = $pdo->prepare("INSERT INTO activity_log (user_id, action, ip_address) VALUES (?, ?, ?)");
        $insertActivity->execute([$user_id, $action, $ip_address]);
        
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
}
?>