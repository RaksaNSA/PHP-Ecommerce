<?php
include '../config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sanitize and validate inputs
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$raw_password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($raw_password)) {
    die("All fields are required.");
}

try {
    // Check if username or email already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
    $check->execute([':username' => $username, ':email' => $email]);
    if ($check->fetch()) {
        die("Username or email already exists.");
    }

    $hashed_password = password_hash($raw_password, PASSWORD_BCRYPT);

    // Set defaults
    $phone = '';
    $address = '';
    $role = 'customer';
    $active = 1;
    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;

    // Insert new user
    $sql = "INSERT INTO users (username, email, password, phone, address, role, active, created_at, updated_at)
            VALUES (:username, :email, :password, :phone, :address, :role, :active, :created_at, :updated_at)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':username'    => $username,
        ':email'       => $email,
        ':password'    => $hashed_password,
        ':phone'       => $phone,
        ':address'     => $address,
        ':role'        => $role,
        ':active'      => $active,
        ':created_at'  => $created_at,
        ':updated_at'  => $updated_at
    ]);
    echo "Register User success";
    // header("Location: authentication-login.html");
    exit();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>