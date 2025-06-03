<?php
// login_process.php

// 1. START THE SESSION
// This MUST be at the very top, before any output.
session_start();

// 2. INCLUDE DATABASE CONNECTION
// This brings in the $pdo object (or mysqli connection) from your config file.
require_once '../config/database.php'; 

// 3. GET USER INPUT
// Data would typically come from a POST request from login.php
$submitted_username = $_POST['username'] ?? '';
$submitted_password = $_POST['password'] ?? '';

// Basic validation (you'd add more robust validation)
if (empty($submitted_username) || empty($submitted_password)) {
    // Handle empty fields - maybe redirect back with an error message
    exit();
}

// 4. PREPARE TO CHECK AGAINST DATABASE
$username_is_valid = false;
$password_is_correct = false;
$user_id_from_database = null;
$username_from_database = null;

try {
    // 5. FETCH USER FROM DATABASE
    $sql = "SELECT id, username, password_hash FROM user WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($sql);
         $stmt->bindParam(':username', $submitted_username, PDO::PARAM_STR);
         $stmt->execute();
         $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // User found, now verify the password
        $username_from_database = $user['username']; // For storing in session if needed
        $user_id_from_database = $user['id'];      // For storing in session

        // 6. VERIFY PASSWORD
        // Compare the submitted password with the stored hash
        if (password_verify($submitted_password, $user['password_hash'])) {
            $password_is_correct = true;
            $username_is_valid = true; // If password is correct, username was inherently valid too
        }
    }
} catch (PDOException $e) {
    // Handle database errors (log them, show a generic error message)
    echo "Database error. Please try again later." . $e->getMessage();
    // error_log("Login DB Error: " . $e->getMessage()); // Example logging
    exit();
}

// 7. YOUR PROVIDED LOGIC (now with context)
if ($username_is_valid && $password_is_correct) {
    // Regenerate session ID for security (prevents session fixation)
    session_regenerate_id(true);

    // Store user's information in the session
    $_SESSION['user_id'] = $user_id_from_database;
    $_SESSION['username'] = $username_from_database;
    $_SESSION['logged_in'] = true; // A simple flag

    // Redirect to a protected page (e.g., the user's dashboard)
    header("Location: dashboard.php");
    exit(); // Crucial to stop further script execution after a redirect
} else {
    // Handle login failure
    // You might redirect back to login.php with an error message
    // For simplicity, just echoing here:
    $_SESSION['login_error'] = "Invalid.";
    header("Location: login.php"); // Redirect back to login form
    exit();
}
?>