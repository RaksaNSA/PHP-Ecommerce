<?php
include_once '../../config/database.php';
// File: admin/pro/process_product.php
// Handles Create, Update, Delete operations

include_once '../../config/config.php'; // Adjusted path

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['flash_message'] = "You must be logged in to perform this action.";
    $_SESSION['flash_message_type'] = "danger";
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

// --- Image Upload Function ---
function handleImageUpload($input_name, $current_image_url = null) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if (
        isset($_FILES[$input_name]) &&
        $_FILES[$input_name]['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        if ($_FILES[$input_name]['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = "Error uploading image.";
            $_SESSION['flash_message_type'] = "danger";
            return false;
        }
        if (!in_array($_FILES[$input_name]['type'], $allowed_types)) {
            $_SESSION['flash_message'] = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
            $_SESSION['flash_message_type'] = "danger";
            return false;
        }
        if ($_FILES[$input_name]['size'] > $max_size) {
            $_SESSION['flash_message'] = "Image size exceeds 2MB limit.";
            $_SESSION['flash_message_type'] = "danger";
            return false;
        }
        $ext = pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('prod_', true) . '.' . $ext;
        $upload_dir = __DIR__ . '/../../images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $upload_path = $upload_dir . $new_filename;
        if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $upload_path)) {
            // Optionally delete old image if updating
            if ($current_image_url && file_exists(__DIR__ . '/../../' . $current_image_url)) {
                unlink(__DIR__ . '/../../' . $current_image_url);
            }
            return 'images/' . $new_filename;
        } else {
            $_SESSION['flash_message'] = "Failed to move uploaded image.";
            $_SESSION['flash_message_type'] = "danger";
            return false;
        }
    }
    // No new image uploaded, return current image if exists (for update)
    return $current_image_url;
}


// --- CREATE Product ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    // $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $image_url = null;

    if (empty($name) || $price === false || $price < 0) {
        $_SESSION['flash_message'] = "Product name and a valid price are required.";
        $_SESSION['flash_message_type'] = "danger";
        header("Location: " . SITE_URL . "/admin/pro/add_product.php");
        exit;
    }

    $uploadedImage = handleImageUpload('image');
    if ($uploadedImage === false) { // Means an error occurred during upload attempt
        header("Location: " . SITE_URL . "/admin/pro/add_product.php"); // Flash message already set by handleImageUpload
        exit;
    }
    $image_url = $uploadedImage;


    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)");
        // If using category: $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $image_url]);
        // If using category: $stmt->execute([$name, $description, $price, $category_id, $image_url]);

        $_SESSION['flash_message'] = "Product added successfully!";
        $_SESSION['flash_message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Error adding product: " . $e->getMessage();
        $_SESSION['flash_message_type'] = "danger";
    }
    header("Location: " . SITE_URL . "/admin/pro/");
    exit;
}

// --- UPDATE Product ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    // $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $current_image_url = isset($_POST['current_image_url']) ? $_POST['current_image_url'] : null;


    if ($product_id <= 0 || empty($name) || $price === false || $price < 0) {
        $_SESSION['flash_message'] = "Invalid data. Product ID, name, and valid price are required.";
        $_SESSION['flash_message_type'] = "danger";
        header("Location: " . SITE_URL . "/admin/pro/edit_product.php?id=" . $product_id);
        exit;
    }
    
    $new_image_url = handleImageUpload('image', $current_image_url);
    if ($new_image_url === false) { // Error during upload attempt
         header("Location: " . SITE_URL . "/admin/pro/edit_product.php?id=" . $product_id); // Flash message set by handleImageUpload
        exit;
    }
    // If no new image was uploaded, $new_image_url will be $current_image_url

    try {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?";
        // If using category: $sql = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image_url = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $price, $new_image_url, $product_id]);
        // If using category: $stmt->execute([$name, $description, $price, $category_id, $new_image_url, $product_id]);

        $_SESSION['flash_message'] = "Product updated successfully!";
        $_SESSION['flash_message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Error updating product: " . $e->getMessage();
        $_SESSION['flash_message_type'] = "danger";
    }
    header("Location: " . SITE_URL . "/admin/pro/");
    exit;
}

// --- DELETE Product ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete') {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($product_id <= 0) {
        $_SESSION['flash_message'] = "Invalid product ID.";
        $_SESSION['flash_message_type'] = "danger";
        header("Location: " . SITE_URL . "/admin/pro/");
        exit;
    }

    try {
        // First, get the image URL to delete the file
        $stmt_select = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt_select->execute([$product_id]);
        $product_image = $stmt_select->fetchColumn();

        // Then, delete the product record
        $stmt_delete = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt_delete->execute([$product_id]);

        if ($stmt_delete->rowCount() > 0) {
            // If product deleted and image exists, delete image file
            if ($product_image && file_exists(UPLOAD_DIR_PRODUCT . $product_image)) {
                unlink(UPLOAD_DIR_PRODUCT . $product_image);
            }
            $_SESSION['flash_message'] = "Product deleted successfully!";
            $_SESSION['flash_message_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Product not found or already deleted.";
            $_SESSION['flash_message_type'] = "warning";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Error deleting product: " . $e->getMessage();
        $_SESSION['flash_message_type'] = "danger";
    }
    header("Location: " . SITE_URL . "/admin/pro/");
    exit;
}

// If no action matched, redirect to product list
$_SESSION['flash_message'] = "Invalid action.";
$_SESSION['flash_message_type'] = "warning";
header("Location: " . SITE_URL . "/admin/pro/");
exit;

?>