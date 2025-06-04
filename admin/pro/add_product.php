<?php
// filepath: c:\wamp64\www\PHP-Ecommerce\admin\pro\add_product.php
session_start();
require_once '../../config/database.php';
require_once '../../config/config.php';

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and validate input
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $image_url   = null; // Default to null, will be set if image is uploade
    $category_id = intval($_POST['category_id'] ?? 0);
    $active      = isset($_POST['active']) ? 1 : 0;
    $display     = isset($_POST['display']) ? 1 : 0;
    $p_order     = intval($_POST['p_order'] ?? 0);

    // Validate required fields
    if ($name === '') $errors[] = "Product name is required.";
    if ($price <= 0) $errors[] = "Price must be greater than 0.";
    if ($stock < 0) $errors[] = "Stock cannot be negative.";
    if ($category_id <= 0) $errors[] = "Please select a category.";

    // Handle image upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid image type. Only JPG, PNG, GIF allowed.";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size must be less than 2MB.";
        } else {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('prod_', true) . '.' . $ext;
            $upload_dir = __DIR__ . '/../../images/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $upload_path = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'images/' . $new_filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    // Insert into database if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products 
                (name, description, price, stock, image_url, active, p_order, display, category_id) 
                VALUES (:name, :description, :price, :stock, :image_url, :active, :p_order, :display, :category_id)");
            $stmt->execute([
                ':name'        => $name,
                ':description' => $description,
                ':price'       => $price,
                ':stock'       => $stock,
                ':image_url'   => $image_url,
                ':active'      => $active,
                ':p_order'     => $p_order,
                ':display'     => $display,
                ':category_id' => $category_id
            ]);
            $success = true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Order number must be unique.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch categories for dropdown
try {
    $cat_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Product - Php Ecommerce</title>
  <link rel="shortcut icon" type="image/png" href="<?php echo SITE_URL; ?>/admin/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/styles.min.css" />
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div class="body-wrapper-inner">
      <div class="container py-5">
        <div class="card shadow">
          <div class="card-body">
            <h4 class="card-title mb-4">Add New Product</h4>
            <?php if ($success): ?>
              <div class="alert alert-success">Product added successfully!</div>
            <?php elseif (!empty($errors)): ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
              </div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
              <div class="col-md-6">
                <label for="productName" class="form-label">Product Name *</label>
                <input type="text" class="form-control" id="productName" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
              </div>
              <div class="col-md-6">
                <label for="productCategory" class="form-label">Category *</label>
                <select class="form-select" id="productCategory" name="category_id" required>
                  <option value="">Select Category</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php if (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) echo 'selected'; ?>>
                      <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-12">
                <label for="productDescription" class="form-label">Description</label>
                <textarea class="form-control" id="productDescription" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
              </div>
              <div class="col-md-4">
                <label for="productPrice" class="form-label">Price *</label>
                <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
              </div>
              <div class="col-md-4">
                <label for="productStock" class="form-label">Stock *</label>
                <input type="number" class="form-control" id="productStock" name="stock" min="0" required value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>">
              </div>
              <div class="col-md-4">
                <label for="productOrder" class="form-label">Order (unique)</label>
                <input type="number" class="form-control" id="productOrder" name="p_order" min="0" value="<?php echo htmlspecialchars($_POST['p_order'] ?? ''); ?>">
              </div>
              <div class="col-md-6">
                <label for="productImage" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="productImage" name="image" accept="image/*">
                <small class="form-text text-muted">Max 2MB. JPG, PNG, GIF only.</small>
              </div>
              <div class="col-md-3 d-flex align-items-center">
                <div class="form-check me-3">
                  <input class="form-check-input" type="checkbox" id="active" name="active" value="1" <?php if (isset($_POST['active']) || !isset($_POST['active'])) echo 'checked'; ?>>
                  <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="display" name="display" value="1" <?php if (isset($_POST['display']) || !isset($_POST['display'])) echo 'checked'; ?>>
                  <label class="form-check-label" for="display">Display</label>
                </div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="<?php echo SITE_URL; ?>/admin/pro/" class="btn btn-secondary">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?php echo SITE_URL; ?>/admin/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo SITE_URL; ?>/admin/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo SITE_URL; ?>/admin/assets/js/sidebarmenu.js"></script>
  <script src="<?php echo SITE_URL; ?>/admin/assets/js/app.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>
</html>