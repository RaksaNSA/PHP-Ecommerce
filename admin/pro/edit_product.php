  <?php
  // filepath: c:\wamp64\www\PHP-Ecommerce\admin\pro\edit_product.php
  session_start();
  include '../../config/database.php';
  include_once '../../config/config.php';

  // Check if the user is logged in
  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
      header("Location: " . SITE_URL . "/admin/login.php");
      exit;
  }

  $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $product = null;

  if ($product_id > 0) {
      try {
          $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
          $stmt->execute([$product_id]);
          $product = $stmt->fetch(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          $_SESSION['flash_message'] = "Error fetching product details: " . $e->getMessage();
          $_SESSION['flash_message_type'] = "danger";
      }
  }

  if (!$product) {
      $_SESSION['flash_message'] = "Product not found.";
      $_SESSION['flash_message_type'] = "warning";
      header("Location: " . SITE_URL . "/admin/pro/");
      exit;
  }

  // Fetch categories for dropdown
  try {
      $cat_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
      $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
      if (empty($categories)) {
      echo "<div style='color:red'>No categories found. Check your category table.</div>";
  }
  } catch (PDOException $e) {
      $categories = [];
  }
  ?>
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Product - Php Ecommerce</title>
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
              <h4 class="card-title mb-4">Edit Product (ID: <?php echo htmlspecialchars($product['id']); ?>)</h4>
              <?php
              if (isset($_SESSION['flash_message'])) {
                  echo '<div class="alert alert-' . htmlspecialchars($_SESSION['flash_message_type']) . ' alert-dismissible fade show" role="alert">'
                      . htmlspecialchars($_SESSION['flash_message']) .
                      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
                  unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);
              }
              ?>
              <form action="<?php echo SITE_URL; ?>/admin/pro/process_product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">

                <div class="mb-3">
                  <label for="productName" class="form-label">Product Name *</label>
                  <input type="text" class="form-control" id="productName" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="mb-3">
                  <label for="productDescription" class="form-label">Description</label>
                  <textarea class="form-control" id="productDescription" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="mb-3">
                  <label for="productPrice" class="form-label">Price *</label>
                  <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required value="<?php echo htmlspecialchars($product['price']); ?>">
                </div>
                <div class="mb-3">
                  <label for="productStock" class="form-label">Stock *</label>
                  <input type="number" class="form-control" id="productStock" name="stock" min="0" required value="<?php echo htmlspecialchars($product['stock']); ?>">
                </div>
                <div class="mb-3">
                  <label for="productOrder" class="form-label">Order (unique)</label>
                  <input type="number" class="form-control" id="productOrder" name="p_order" min="0" value="<?php echo htmlspecialchars($product['p_order']); ?>">
                </div>
                <div class="mb-3">
                  <label for="productCategory" class="form-label">Category *</label>
                  <select class="form-select" id="productCategory" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?php echo $cat['id']; ?>" <?php if ($product['category_id'] == $cat['id']);?> selected>
                        <?php echo htmlspecialchars($cat['name']);?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="productImage" class="form-label">Product Image</label>
                  <input type="file" class="form-control" id="productImage" name="image">
                  <small class="form-text text-muted">Leave empty to keep current image. Max file size 2MB. Allowed types: JPG, PNG, GIF.</small>
                  <?php if (!empty($product['image_url'])): ?>
                    <div class="mt-2">
                      Current Image: <img src="<?php echo UPLOAD_URL_PRODUCT . htmlspecialchars($product['image_url']); ?>" alt="Current Image" width="100" class="img-thumbnail">
                    </div>
                  <?php endif; ?>
                </div>
                <div class="mb-3 form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="active" name="active" value="1" <?php if ($product['active']) echo 'checked'; ?>>
                  <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="mb-3 form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="display" name="display" value="1" <?php if ($product['display']) echo 'checked'; ?>>
                  <label class="form-check-label" for="display">Display</label>
                </div>
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="<?php echo SITE_URL; ?>/admin/pro/" class="btn btn-secondary">Cancel</a>
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