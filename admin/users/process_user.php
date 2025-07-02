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

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $redirect_url = (defined('SITE_URL') ? SITE_URL : '') . "/admin/login.php";
    header("Location: " . $redirect_url);
    exit;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone (basic validation)
function validatePhone($phone) {
    return preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone);
}

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Get the action and user ID
$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    switch ($action) {
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Get form data
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                $phone = trim($_POST['phone']);
                $address = trim($_POST['address']);
                $role = $_POST['role'];
                $active = isset($_POST['active']) ? 1 : 0;
                
                // Validation
                $errors = [];
                
                if (empty($username)) {
                    $errors[] = "Username is required.";
                } elseif (strlen($username) < 3) {
                    $errors[] = "Username must be at least 3 characters long.";
                }
                
                if (empty($email)) {
                    $errors[] = "Email is required.";
                } elseif (!validateEmail($email)) {
                    $errors[] = "Please enter a valid email address.";
                }
                
                if (empty($password)) {
                    $errors[] = "Password is required.";
                } elseif (strlen($password) < 6) {
                    $errors[] = "Password must be at least 6 characters long.";
                }
                
                if ($password !== $confirm_password) {
                    $errors[] = "Passwords do not match.";
                }
                
                if (!empty($phone) && !validatePhone($phone)) {
                    $errors[] = "Please enter a valid phone number.";
                }
                
                if (!in_array($role, ['admin', 'user'])) {
                    $errors[] = "Invalid role selected.";
                }
                
                // Check if username already exists
                if (empty($errors)) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $errors[] = "Username already exists.";
                    }
                }
                
                // Check if email already exists
                if (empty($errors)) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $errors[] = "Email already exists.";
                    }
                }
                
                if (empty($errors)) {
                    // Insert new user
                    $hashed_password = hashPassword($password);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, phone, address, role, active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    
                    if ($stmt->execute([$username, $email, $hashed_password, $phone, $address, $role, $active])) {
                        $_SESSION['flash_message'] = "User '$username' has been added successfully.";
                        $_SESSION['flash_message_type'] = "success";
                    } else {
                        $_SESSION['flash_message'] = "Error adding user. Please try again.";
                        $_SESSION['flash_message_type'] = "danger";
                    }
                } else {
                    $_SESSION['flash_message'] = implode('<br>', $errors);
                    $_SESSION['flash_message_type'] = "danger";
                }
                
                header("Location: " . SITE_URL . "/admin/users/");
                exit;
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = intval($_POST['user_id']);
                
                // Get form data
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                $phone = trim($_POST['phone']);
                $address = trim($_POST['address']);
                $role = $_POST['role'];
                $active = isset($_POST['active']) ? 1 : 0;
                
                // Validation
                $errors = [];
                
                if (empty($username)) {
                    $errors[] = "Username is required.";
                } elseif (strlen($username) < 3) {
                    $errors[] = "Username must be at least 3 characters long.";
                }
                
                if (empty($email)) {
                    $errors[] = "Email is required.";
                } elseif (!validateEmail($email)) {
                    $errors[] = "Please enter a valid email address.";
                }
                
                // Only validate password if it's provided (for updates)
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $errors[] = "Password must be at least 6 characters long.";
                    }
                    
                    if ($password !== $confirm_password) {
                        $errors[] = "Passwords do not match.";
                    }
                }
                
                if (!empty($phone) && !validatePhone($phone)) {
                    $errors[] = "Please enter a valid phone number.";
                }
                
                if (!in_array($role, ['admin', 'user'])) {
                    $errors[] = "Invalid role selected.";
                }
                
                // Check if username already exists (excluding current user)
                if (empty($errors)) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                    $stmt->execute([$username, $user_id]);
                    if ($stmt->fetch()) {
                        $errors[] = "Username already exists.";
                    }
                }
                
                // Check if email already exists (excluding current user)
                if (empty($errors)) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $user_id]);
                    if ($stmt->fetch()) {
                        $errors[] = "Email already exists.";
                    }
                }
                
                if (empty($errors)) {
                    // Update user
                    if (!empty($password)) {
                        // Update with new password
                        $hashed_password = hashPassword($password);
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, phone = ?, address = ?, role = ?, active = ? WHERE id = ?");
                        $result = $stmt->execute([$username, $email, $hashed_password, $phone, $address, $role, $active, $user_id]);
                    } else {
                        // Update without changing password
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, address = ?, role = ?, active = ? WHERE id = ?");
                        $result = $stmt->execute([$username, $email, $phone, $address, $role, $active, $user_id]);
                    }
                    
                    if ($result) {
                        $_SESSION['flash_message'] = "User '$username' has been updated successfully.";
                        $_SESSION['flash_message_type'] = "success";
                    } else {
                        $_SESSION['flash_message'] = "Error updating user. Please try again.";
                        $_SESSION['flash_message_type'] = "danger";
                    }
                } else {
                    $_SESSION['flash_message'] = implode('<br>', $errors);
                    $_SESSION['flash_message_type'] = "danger";
                }
                
                header("Location: " . SITE_URL . "/admin/users/");
                exit;
            }
            break;
            
        case 'delete':
            if ($user_id > 0) {
                // Prevent deletion of current user
                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['flash_message'] = "You cannot delete your own account.";
                    $_SESSION['flash_message_type'] = "warning";
                } else {
                    // Get user info before deletion
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                    
                    if ($user) {
                        // Delete user
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        if ($stmt->execute([$user_id])) {
                            $_SESSION['flash_message'] = "User '" . htmlspecialchars($user['username']) . "' has been deleted successfully.";
                            $_SESSION['flash_message_type'] = "success";
                        } else {
                            $_SESSION['flash_message'] = "Error deleting user. Please try again.";
                            $_SESSION['flash_message_type'] = "danger";
                        }
                    } else {
                        $_SESSION['flash_message'] = "User not found.";
                        $_SESSION['flash_message_type'] = "danger";
                    }
                }
            } else {
                $_SESSION['flash_message'] = "Invalid user ID.";
                $_SESSION['flash_message_type'] = "danger";
            }
            
            header("Location: " . SITE_URL . "/admin/users/");
            exit;
            break;
            
        case 'toggle_status':
            if ($user_id > 0) {
                // Prevent deactivating current user
                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['flash_message'] = "You cannot deactivate your own account.";
                    $_SESSION['flash_message_type'] = "warning";
                } else {
                    // Toggle user status
                    $stmt = $pdo->prepare("UPDATE users SET active = NOT active WHERE id = ?");
                    if ($stmt->execute([$user_id])) {
                        $_SESSION['flash_message'] = "User status has been updated successfully.";
                        $_SESSION['flash_message_type'] = "success";
                    } else {
                        $_SESSION['flash_message'] = "Error updating user status. Please try again.";
                        $_SESSION['flash_message_type'] = "danger";
                    }
                }
            } else {
                $_SESSION['flash_message'] = "Invalid user ID.";
                $_SESSION['flash_message_type'] = "danger";
            }
            
            header("Location: " . SITE_URL . "/admin/users/");
            exit;
            break;
            
        default:
            $_SESSION['flash_message'] = "Invalid action.";
            $_SESSION['flash_message_type'] = "danger";
            header("Location: " . SITE_URL . "/admin/users/");
            exit;
            break;
    }
    
} catch (PDOException $e) {
    if ($debug_mode) {
        $_SESSION['flash_message'] = "Database error: " . $e->getMessage();
    } else {
        $_SESSION['flash_message'] = "A database error occurred. Please try again.";
    }
    $_SESSION['flash_message_type'] = "danger";
    header("Location: " . SITE_URL . "/admin/users/");
    exit;
} catch (Exception $e) {
    if ($debug_mode) {
        $_SESSION['flash_message'] = "Error: " . $e->getMessage();
    } else {
        $_SESSION['flash_message'] = "An error occurred. Please try again.";
    }
    $_SESSION['flash_message_type'] = "danger";
    header("Location: " . SITE_URL . "/admin/users/");
    exit;
}

// If we reach here without a proper action, redirect back to users list
header("Location: " . SITE_URL . "/admin/users/");
exit;
?>