<?php
session_start();

// Debug mode - set to false in production
$debug_mode = true;

if ($debug_mode) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Check if config files exist
if (!file_exists('../../config/database.php')) {
    die('Database config file not found at: ' . realpath('../../config/database.php'));
}
if (!file_exists('../../config/config.php')) {
    die('Config file not found at: ' . realpath('../../config/config.php'));
}

include '../../config/database.php';
include_once '../../config/config.php';

// Debug session info (remove in production)
if ($debug_mode) {
    echo "<!-- Debug Info: ";
    echo "Session logged_in: " . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') . " | ";
    echo "Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NOT SET') . " | ";
    echo "SITE_URL: " . (defined('SITE_URL') ? SITE_URL : 'NOT DEFINED');
    echo " -->";
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $redirect_url = (defined('SITE_URL') ? SITE_URL : '') . "/admin/login.php";
    header("Location: " . $redirect_url);
    exit;
}

// Fetch users from the database
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error, e.g., display a message or log it
    $users = []; // Set an empty array on error
    $_SESSION['flash_message'] = "Error fetching users: " . $e->getMessage();
    $_SESSION['flash_message_type'] = "danger";
}

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Management - Php Ecommerce</title>
  <link rel="shortcut icon" type="image/png" href="<?php echo SITE_URL; ?>/admin/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/styles.min.css" />
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <div class="app-topstrip bg-dark py-6 px-3 w-100 d-lg-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center justify-content-center gap-5 mb-2 mb-lg-0">
        <a class="d-flex justify-content-center" href="<?php echo SITE_URL; ?>/admin/">
          <img src="<?php echo SITE_URL; ?>/admin/assets/images/logos/logo-wrappixel.png" alt="" width="80">
        </a>
      </div>
      <div class="d-lg-flex align-items-center gap-2">
        <h3 class="text-white mb-2 mb-lg-0 fs-5 text-center">Admin Dashboard - Users</h3>
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
              <a class="sidebar-link" href="<?php echo SITE_URL; ?>/admin/dashboard" aria-expanded="false">
                <i class="ti ti-atom"></i>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" href="<?php echo SITE_URL; ?>/admin/pro/" aria-expanded="false">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <i class="ti ti-aperture"></i>
                  </span>
                  <span class="hide-menu">Product</span>
                </div>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between active" href="<?php echo SITE_URL; ?>/admin/users/" aria-expanded="false">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <i class="ti ti-users"></i>
                  </span>
                  <span class="hide-menu">User</span>
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
              <div class="d-md-flex align-items-center">
                  <div>
                      <h4 class="card-title">User List</h4>
                      <p class="card-subtitle">Manage your users here.</p>
                  </div>
                  <div class="ms-auto">
                      <a href="<?php echo SITE_URL; ?>/admin/users/add_user.php" class="btn btn-primary">Add New User</a>
                  </div>
              </div>
              <div class="mt-3">
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
              </div>

              <div class="table-responsive mt-4">
                <table class="table mb-0 text-nowrap varient-table align-middle fs-3">
                        <thead>
                            <tr>
                                <th scope="col" class="px-0 text-muted">ID</th>
                                <th scope="col" class="px-0 text-muted">Username</th>
                                <th scope="col" class="px-0 text-muted">Email</th>
                                <th scope="col" class="px-0 text-muted">Phone</th>
                                <th scope="col" class="px-0 text-muted">Address</th>
                                <th scope="col" class="px-0 text-muted">Role</th>
                                <th scope="col" class="px-0 text-muted">Status</th>
                                <th scope="col" class="px-0 text-muted">Created At</th>
                                <th scope="col" class="px-0 text-muted">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-0"><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td class="px-0"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="px-0"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-0"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                    <td class="px-0">
                                        <?php
                                        // Truncate address for display if it's too long
                                        $address = htmlspecialchars($user['address'] ?? '');
                                        if (strlen($address) > 30) {
                                            echo substr($address, 0, 30) . "...";
                                        } else {
                                            echo $address ?: 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-0">
                                        <span class="badge bg-<?php echo ($user['role'] === 'admin') ? 'danger' : 'primary'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($user['role'] ?? 'user')); ?>
                                        </span>
                                    </td>
                                    <td class="px-0">
                                        <span class="badge bg-<?php echo $user['active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $user['active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="px-0">
                                        <?php
                                        if (!empty($user['created_at'])) {
                                            $date = new DateTime($user['created_at']);
                                            echo htmlspecialchars($date->format('Y-m-d H:i'));
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-0">
                                        <a href="<?php echo SITE_URL; ?>/admin/users/edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): // Don't allow deleting current user ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/users/process_user.php?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="py-6 px-6 text-center">
            <p class="mb-0 fs-4">Design and Developed by <a href="#"
                class="pe-1 text-primary text-decoration-underline">Chhem Raksa</a> Distributed by <a href="<?php echo SITE_URL?>" target="_blank">Raksa Shop</a></p>
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