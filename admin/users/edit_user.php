<?php
session_start();
include '../../config/database.php';
include_once '../../config/config.php';

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;

if ($user_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Error fetching user details: " . $e->getMessage();
        $_SESSION['flash_message_type'] = "danger";
    }
}

if (!$user) {
    $_SESSION['flash_message'] = "User not found.";
    $_SESSION['flash_message_type'] = "warning";
    header("Location: " . SITE_URL . "/admin/users/");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit User - Php Ecommerce</title>
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
            <h4 class="card-title mb-4">Edit User (ID: <?php echo htmlspecialchars($user['id']); ?>)</h4>
            <?php
            if (isset($_SESSION['flash_message'])) {
                echo '<div class="alert alert-' . htmlspecialchars($_SESSION['flash_message_type']) . ' alert-dismissible fade show" role="alert">'
                    . htmlspecialchars($_SESSION['flash_message']) .
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
                unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);
            }
            ?>
            <form action="<?php echo SITE_URL; ?>/admin/users/process_user.php" method="POST" id="editUserForm">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>">
                    <small class="form-text text-muted">Must be unique and at least 3 characters long.</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                    <small class="form-text text-muted">Must be a valid email address.</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="8">
                    <small class="form-text text-muted">Leave empty to keep current password. Minimum 8 characters if changing.</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8">
                    <small class="form-text text-muted">Must match the new password if changing.</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    <small class="form-text text-muted">Optional phone number.</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="role" class="form-label">Role *</label>
                    <select class="form-select" id="role" name="role" required>
                      <option value="">Select Role</option>
                      <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                      <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter full address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                <small class="form-text text-muted">Optional address information.</small>
              </div>

              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" <?php echo $user['active'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="active">Active</label>
              </div>

              <div class="mb-3">
                <small class="text-muted">
                  Created: <?php echo $user['created_at'] ? date('Y-m-d H:i', strtotime($user['created_at'])) : 'N/A'; ?> | 
                  Last Updated: <?php echo $user['updated_at'] ? date('Y-m-d H:i', strtotime($user['updated_at'])) : 'N/A'; ?>
                </small>
              </div>

              <button type="submit" class="btn btn-primary">Update User</button>
              <a href="<?php echo SITE_URL; ?>/admin/users/" class="btn btn-secondary">Cancel</a>
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
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1