<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "php_ecommerce";

$pdo = new PDO("mysql:host=$host", $username, $password);
try{
    $sql = "create database if not exists $dbname";

    $pdo->exec($sql);
    echo "Database created successfully";
}catch(Exception $e){
    die("Connetion Failed:". $e->getMessage());
}
?>