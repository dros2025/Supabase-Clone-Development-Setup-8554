<?php
require_once 'db.php';

// Start session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['email']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit();
}

$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$password = $input['password'];

// Debug logging
error_log("Login attempt - Email: " . $email);
error_log("Login attempt - Entered password: " . $password);

try {
    // Fetch user from database
    $getUser = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $getUser->execute([$email]);
    
    $user = $getUser->fetch();
    
    if (!$user) {
        error_log("Login failed - User not found: " . $email);
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
        exit();
    }
    
    // Debug logging
    error_log("Stored hash: " . $user['password']);
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        error_log("Login failed - Password verification failed for user: " . $email);
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
        exit();
    }
    
    // Set session variables
    $_SESSION["user_id"] = $user['id'];
    $_SESSION["email"] = $user['email'];
    $_SESSION["logged_in"] = true;
    
    // Force session write
    session_write_close();
    
    error_log("Login successful - User: " . $email);
    
    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user_id" => $user['id'],
        "email" => $user['email'],
        "session_id" => session_id()
    ]);
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Login failed",
        "error" => $e->getMessage()
    ]);
}
?>