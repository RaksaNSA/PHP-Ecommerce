<?php
include '../config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Sanitize and validate inputs
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$raw_password = $_POST['password'];

if (empty($name) || empty($email) || empty($raw_password)) {
    die("All fields are required.");
}

$hashed_password = password_hash($raw_password, PASSWORD_BCRYPT);

// Set defaults
$phone = '';
$address = '';
$role = 'user';
$active = 1;
$created_at = date('Y-m-d H:i:s');
$updated_at = $created_at;

// Prepare and execute SQL statement
$sql = "INSERT INTO user (username, email, password_hash, phone, address, role, active, created_at, updated_at)
        VALUES (:username, :email, :password_hash, :phone, :address, :role, :active, :created_at, :updated_at)";
$stmt = $pdo->prepare($sql);

  $stmt->execute([
    ':username'   => $name,
    ':email'      => $email,
    ':password_hash' => $hashed_password,
    ':phone'      => $phone,
    ':address'    => $address,
    ':role'       => $role,
    ':active'     => $active,
    ':created_at' => $created_at,
    ':updated_at' => $updated_at
]);

// $stmt->execute( params: [
//     ':username'   => $name,
//     ':email'      => $email,
//     ':password'   => $hashed_password,
//     ':phone'      => $phone,
//     ':address'    => $address,
//     ':role'       => $role,
//     ':active'     => $active,
//     ':created_at' => $created_at,
//     ':updated_at' => $updated_at,
// ]);

echo "Register User success";

// header("Location: authentication-login.html");
exit();
?>