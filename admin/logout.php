<?php
// File: admin/logout.php

// 1. Include configuration file
// This should ideally start the session (if not already started by the file itself),
// make $pdo available for database operations, and define SITE_URL.
// Use require_once for essential configuration.
// Assuming logout.php is in 'admin/' and config.php is in 'config/'
require_once '../config/config.php';

// The config.php file should have already started the session.
// If not, uncomment the next line, but it's better if config.php handles session_start().
// if (session_status() == PHP_SESSION_NONE) { session_start(); }


// 2. Clear "Remember Me" token from database and cookies

// Get the selector from the cookie, if it exists
$selector_cookie_value = $_COOKIE['remember_selector'] ?? null;

// Attempt to use the helper function if it was defined in config.php
if (function_exists('clear_auth_cookies_and_db_token')) {
    // The function clear_auth_cookies_and_db_token expects $pdo and the selector
    clear_auth_cookies_and_db_token($pdo, $selector_cookie_value);
} else {
    // Fallback: Manually clear cookies and DB token if the helper function isn't available
    // This manual part is less ideal as it duplicates logic; ensure your helper function is in config.php

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
    // Fallback or error if SITE_URL is not defined, adjust as necessary
    // This is a basic fallback and assumes login.php is in the same directory.
    // It's much better to ensure SITE_URL is always defined.
    header("Location: login.php");
    exit();
}

header("Location: " . SITE_URL . "/admin/login.php");
exit();
?>