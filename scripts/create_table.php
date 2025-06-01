<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "php_ecommerce";
// It's generally better to  connect without specifying $dbname if you're about to CREATE DATABASE
// and then USE it, or just connect directly to it if you're sure it exists.
// However, per your requestnot to change code structure outside the error:
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

try {
    // create database if it does not exist
    // Note: Connecting with $dbname in the DSN and then trying to create it can be redundant
    // or lead to errors if $dbname doesn't exist yet and the user lacks permissions.
    // It's often better to connect to mysql server (no dbname in DSN) to CREATE DATABASE.
    // Then connect again with $dbname in DSN or use $pdo->exec("USE $dbname");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname"); // This ensures subsequent queries are on the correct DB.

    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // create user table
    $sqlUser = "CREATE TABLE IF NOT EXISTS user (
                    id INTEGER AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100),
                    email VARCHAR(100) UNIQUE,
                    password VARCHAR(255),
                    phone VARCHAR(20),
                    address TEXT,
                    role VARCHAR(50) DEFAULT 'customer',
                    active INTEGER DEFAULT 1, -- <<< COMMA ADDED HERE
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
    $pdo->exec($sqlUser); // Changed to exec() which is more appropriate for DDL

    // create order_product table
    // NOTE: The definition for order_product references `order`(id) and product(id).
    // You should create the `order` and `product` tables BEFORE this one.
    // I will reorder them below for logical dependency.


    // create order_item table (actually 'order' table)
    $sqlOrderItem = "CREATE TABLE IF NOT EXISTS `order` (
                        id INTEGER AUTO_INCREMENT PRIMARY KEY,
                        user_id INTEGER,
                        total DOUBLE,
                        status VARCHAR(50) DEFAULT 'pending',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
                    )";
    $pdo->exec($sqlOrderItem); // Changed to exec()

    // create category table
    $sqlCategory = "CREATE TABLE IF NOT EXISTS category (
                        id INTEGER AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100),
                        active INTEGER DEFAULT 1
                    )";
    $pdo->exec($sqlCategory); // Changed to exec()

    // create product table
    $sqlProduct = "CREATE TABLE IF NOT EXISTS product (
                        id INTEGER AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255),
                        description TEXT,
                        price DOUBLE,
                        stock INTEGER,
                        image TEXT,
                        active INTEGER DEFAULT 1,
                        p_order INTEGER UNIQUE,
                        display INTEGER DEFAULT 1,
                        category_id INTEGER,
                        FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE CASCADE
                    )";
    $pdo->exec($sqlProduct); // Changed to exec()

    // create order_product table (Now that `order` and `product` tables exist)
    $sqlOrderProduct = "CREATE TABLE IF NOT EXISTS order_product (
                            id INTEGER AUTO_INCREMENT PRIMARY KEY,
                            order_id INTEGER,
                            product_id INTEGER,
                            quantity INTEGER,
                            price DOUBLE,
                            FOREIGN KEY (order_id) REFERENCES `order`(id) ON DELETE CASCADE,
                            FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
                        )";
    $pdo->exec($sqlOrderProduct); // Changed to exec()


    // create payment table
    $sqlPayment = "CREATE TABLE IF NOT EXISTS payment (
                        id INTEGER AUTO_INCREMENT PRIMARY KEY,
                        order_id INTEGER,
                        amount DOUBLE,
                        method VARCHAR(50),
                        status VARCHAR(50) DEFAULT 'pending',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (order_id) REFERENCES `order`(id) ON DELETE CASCADE
                    )";
    $pdo->exec($sqlPayment); // Changed to exec()

    // create cart table
    $sqlCart = "CREATE TABLE IF NOT EXISTS cart (
                    id INTEGER AUTO_INCREMENT PRIMARY KEY,
                    user_id INTEGER,
                    product_id INTEGER,
                    quantity INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
                )";
    $pdo->exec($sqlCart); // Changed to exec()

    // create review table
    $sqlReview = "CREATE TABLE IF NOT EXISTS review (
                        id INTEGER AUTO_INCREMENT PRIMARY KEY,
                        user_id INTEGER,
                        product_id INTEGER,
                        rating INTEGER,
                        comment TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
                    )";
    $pdo->exec($sqlReview); // Changed to exec()

    // create shipping table
    $sqlShipping = "CREATE TABLE IF NOT EXISTS shipping (
                        id INTEGER AUTO_INCREMENT PRIMARY KEY,
                        order_id INTEGER,
                        address TEXT,
                        city VARCHAR(100),
                        state VARCHAR(100),
                        zip VARCHAR(20),
                        country VARCHAR(100),
                        status VARCHAR(50) DEFAULT 'pending',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (order_id) REFERENCES `order`(id) ON DELETE CASCADE
                    )";
    $pdo->exec($sqlShipping); // Changed to exec()

    // create wishlist table
    $sqlWishlist = "CREATE TABLE IF NOT EXISTS wishlist (
                        id INTEGER AUTO_INCREMENT PRIMARY KEY,
                        user_id INTEGER,
                        product_id INTEGER,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
                    )";
    $pdo->exec($sqlWishlist); // Changed to exec()

    echo "Tables created successfully";

} catch (PDOException $e) {
    die("Operation failed: " . $e->getMessage());
}
?>