<?php
require_once 'db.php';

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

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format"
    ]);
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters long"
    ]);
    exit();
}

try {
    // Check if user already exists
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkUser->execute([$email]);
    
    if ($checkUser->rowCount() > 0) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "User with this email already exists"
        ]);
        exit();
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    error_log("Registration - Hashed password: " . $hashedPassword);
    
    // Insert new user
    $insertUser = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $insertUser->execute([$email, $hashedPassword]);
    
    // Get the inserted user ID
    $userId = $pdo->lastInsertId();
    
    // Return success response
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "User registered successfully",
        "user_id" => $userId,
        "email" => $email
    ]);
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Registration failed",
        "error" => $e->getMessage()
    ]);
}
?>