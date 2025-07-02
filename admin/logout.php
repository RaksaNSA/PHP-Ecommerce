<?php
require_once '../config/config.php';
$selector_cookie_value = $_COOKIE['remember_selector'] ?? null;
if (function_exists('clear_auth_cookies_and_db_token')) {
    clear_auth_cookies_and_db_token($pdo, $selector_cookie_value);
} else {
    // a. Clear cookies by setting their expiration to the past
    $cookie_options_expire = [
        'expires' => time() - 3600, // 1 hour ago
        'path' => '/',              // Should match the path used when setting the cookie
        'domain' => '',             // Should match the domain used, or empty for current
        'secure' => isset($_SERVER['HTTPS']), // True if site is HTTPS
        'httponly' => true,         // Good practice
        'samesite' => 'Lax'         // Or 'Strict'
    ];
    setcookie('remember_selector', '', $cookie_options_expire);
    setcookie('remember_validator', '', $cookie_options_expire);
    // b. If a selector cookie was present, try to delete the token from the database
    if ($selector_cookie_value && isset($pdo)) {
        try {
            $sql_delete_token = "DELETE FROM auth_tokens WHERE selector = :selector";
            $stmt_delete_token = $pdo->prepare($sql_delete_token);
            $stmt_delete_token->execute([':selector' => $selector_cookie_value]);
        } catch (PDOException $e) {
            // Log the error, but don't prevent logout
            error_log("Logout Error: Failed to delete auth token for selector {$selector_cookie_value}. Error: " . $e->getMessage());
        }
    }
}

// 3. Unset all of the session variables.
$_SESSION = array();

// 4. If you are using session cookies (default behavior), delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Finally, destroy the session.
session_destroy();
// 6. Redirect to the login page
// Ensure SITE_URL is correctly defined in config.php
if (!defined('SITE_URL')) {
    header("Location: login.php");
    exit();
}

header("Location: " . SITE_URL . "/admin/login.php");
exit();
?>