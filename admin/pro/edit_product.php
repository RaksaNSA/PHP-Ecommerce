<?php
// File: admin/pro/edit_product.php
// This is the Edit Product page (Update operation - Form)
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
        $product = $stmt->fetch();
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Error fetching product details: " . $e->getMessage();
        $_SESSION['flash_message_type'] = "danger";
        // Redirect or handle error - for simplicity, we'll proceed and the form will be empty if $product is null
    }
}

if (!$product) {
    $_SESSION['flash_message'] = "Product not found.";
    $_SESSION['flash_message_type'] = "warning";
    header("Location: " . SITE_URL . "/admin/pro/");
    exit;
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

    <div class="app-topstrip bg-dark py-6 px-3 w-100 d-lg-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center justify-content-center gap-5 mb-2 mb-lg-0">
        <a class="d-flex justify-content-center" href="<?php echo SITE_URL; ?>/admin/">
          <img src="<?php echo SITE_URL; ?>/admin/assets/images/logos/logo-wrappixel.svg" alt="" width="150">
        </a>
      </div>
      <div class="d-lg-flex align-items-center gap-2">
        <h3 class="text-white mb-2 mb-lg-0 fs-5 text-center">Admin Dashboard - Edit Product</h3>
         <div class="d-flex align-items-center justify-content-center gap-2">
          <div class="dropdown d-flex">
            <a class="btn btn-primary d-flex align-items-center gap-1 " href="<?php echo SITE_URL?>" target="_blank" id="drop4">
              <i class="ti ti-shopping-cart fs-5"></i>
              Go To Shop
            </a>
          </div>
        </div>
      </div>
    </div>
    <aside class="left-sidebar">
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="<?php echo SITE_URL; ?>/admin/" class="text-nowrap logo-img">
            <img src="<?php echo SITE_URL; ?>/admin/assets/images/logos/logo.svg" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-6"></i>
          </div>
        </div>
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?php echo SITE_URL; ?>/admin/" aria-expanded="false">
                <i class="ti ti-atom"></i>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between active" href="<?php echo SITE_URL; ?>/admin/pro/" aria-expanded="false">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <i class="ti ti-aperture"></i>
                  </span>
                  <span class="hide-menu">Product</span>
                </div>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?php echo SITE_URL; ?>/admin/logout.php" aria-expanded="false">
                <i class="ti ti-logout"></i>
                <span class="hide-menu">Logout</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </aside>
    <div class="body-wrapper">
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler " id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
          </ul>
           <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <li class="nav-item dropdown">
                <a class="nav-link " href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <img src="<?php echo SITE_URL; ?>/admin/assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                   Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                      <i class="ti ti-user fs-6"></i>
                      <p class="mb-0 fs-3">My Profile</p>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Edit Product (ID: <?php echo htmlspecialchars($product['id']); ?>)</h4>
              <p class="card-subtitle mb-4">Update the product details below.</p>
                <?php
                if (isset($_SESSION['flash_message'])) {
                    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['flash_message_type']) . ' alert-dismissible fade show" role="alert">
                            ' . htmlspecialchars($_SESSION['flash_message']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                    unset($_SESSION['flash_message']);
                    unset($_SESSION['flash_message_type']);
                }
                ?>
              <form action="<?php echo SITE_URL; ?>/admin/pro/process_product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">

                <div class="mb-3">
                  <label for="productName" class="form-label">Product Name</label>
                  <input type="text" class="form-control" id="productName" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                
                <div class="mb-3">
                  <label for="productDescription" class="form-label">Description</label>
                  <textarea class="form-control" id="productDescription" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div class="mb-3">
                  <label for="productPrice" class="form-label">Price</label>
                  <input type="number" class="form-control" id="productPrice" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
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
                
                <?php
                    // Example: Fetch categories and pre-select
                    // try {
                    //   $cat_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
                    //   $categories = $cat_stmt->fetchAll();
                    //   foreach ($categories as $category) {
                    //     $selected = ($product['category_id'] == $category['id']) ? 'selected' : '';
                    //     echo '<option value="' . htmlspecialchars($category['id']) . '" ' . $selected . '>' . htmlspecialchars($category['name']) . '</option>';
                    //   }
                    // } catch (PDOException $e) { /* Handle error */ }
                    ?>
                  <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="<?php echo SITE_URL; ?>/admin/pro/" class="btn btn-secondary">Cancel</a>
              </form>
            </div>
          </div>
          <div class="py-6 px-6 text-center">
             <p class="mb-0 fs-4">Design and Developed by <a href="#"
                class="pe-1 text-primary text-decoration-underline">Wrappixel.com</a> Distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a></p>
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