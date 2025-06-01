<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "php_ecommerce";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // echo "Connection successful";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}