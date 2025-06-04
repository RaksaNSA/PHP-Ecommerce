<?php
define("SITE_NAME","E-Commerce");
define("SITE_URL","http://localhost/php-ecommerce");
// File: config/config.php
require_once 'database.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!defined('SITE_URL')) { // Make sure SITE_URL is defined
    // Adjust this to your actual URL. This is crucial for redirects.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script_name_parts = explode('/', $_SERVER['SCRIPT_NAME']);
    // Assuming your project is one level down from the web root (e.g., /your_project_folder/admin/...)
    // If your_project_folder is the root, adjust accordingly.
    $project_folder_name = $script_name_parts[1] ?? 'your_php_ecommerce_project'; // Default if structure is different
    define('SITE_URL', $protocol . $host . '/' . $project_folder_name);
}


// if (!isset($pdo)) { // Ensure $pdo is defined, if not included from database.php
//     // Database Credentials
//     if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
//     if (!defined('DB_NAME')) define('DB_NAME', 'your_ecommerce_db'); // Replace
//     if (!defined('DB_USER')) define('DB_USER', 'your_db_user');     // Replace
//     if (!defined('DB_PASSWORD')) define('DB_PASSWORD', 'your_db_password'); // Replace

//     try {
//         $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
//         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//         $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//     } catch (PDOException $e) {
//         // For a config file, it's usually better to die with an error if DB connection fails.
//         error_log("FATAL: Could not connect to database in config.php. " . $e->getMessage());
//         die("A critical error occurred with the database connection. Please check server logs.");
//     }
// }


// --- End of example $pdo setup ---


// 4. "REMEMBER ME" COOKIE CHECK (AUTOMATIC LOGIN LOGIC)
// This should come AFTER session_start() and AFTER $pdo is available.
if (!isset($_SESSION['logged_in']) && isset($_COOKIE['remember_selector']) && isset($_COOKIE['remember_validator'])) {

    $selector = $_COOKIE['remember_selector'];
    $validator_from_cookie = $_COOKIE['remember_validator']; // Raw validator from cookie

    try {
        // global $pdo;
        $sql = "SELECT * FROM auth_tokens WHERE selector = :selector AND expires >= NOW() LIMIT 1";
        $stmt = $pdo->prepare($sql); 
        $stmt->execute([':selector' => $selector]);
        $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($token_data) {
            // Token found in DB, now verify the validator from the cookie
            $hashed_validator_from_cookie = hash('sha256', $validator_from_cookie);

            if (hash_equals($token_data['hashed_token'], $hashed_validator_from_cookie)) {
                // Token is valid! Log the user in.
                // Regenerate session ID for security (especially important when upgrading privileges)
                session_regenerate_id(true);

                // Fetch user details to populate the session
                $user_sql = "SELECT id, name FROM user WHERE id = :user_id LIMIT 1"; // Assuming 'user' table and 'name' column for username
                $user_stmt = $pdo->prepare($user_sql);
                $user_stmt->execute([':user_id' => $token_data['user_id']]);
                $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['name']; // Or whatever field you use for display username
                    $_SESSION['logged_in'] = true;

                    // OPTIONAL: Implement rolling tokens for enhanced security.
                    // This involves deleting the current token and issuing a new one.
                    // To do this, you'd need the `generate_token` and `store_auth_token` functions
                    // (or similar logic) available here.
                    // Example (requires helper functions to be defined/included):
                    /*
                    $delete_sql = "DELETE FROM auth_tokens WHERE id = :token_id";
                    $delete_stmt = $pdo->prepare($delete_sql);
                    $delete_stmt->execute([':token_id' => $token_data['id']]);

                    // Issue a new token pair (ensure generate_token and store_auth_token are available)
                    // function generate_token($length = 32) { return bin2hex(random_bytes($length)); } // Define if not elsewhere
                    // function store_auth_token($pdo, $user_id, $selector, $hashed_validator, $expires_timestamp) { ... } // Define if not elsewhere

                    $new_validator = generate_token(32); // As used in login_process.php
                    $new_hashed_validator = hash('sha256', $new_validator);
                    $new_expires_timestamp = time() + (86400 * 30); // 30 days

                    // Re-use the same selector, or generate a new one if preferred.
                    // Using the same selector means only the validator cookie needs updating.
                    store_auth_token($pdo, $user['id'], $selector, $new_hashed_validator, $new_expires_timestamp);

                    $cookie_options_update = [
                        'expires' => $new_expires_timestamp,
                        'path' => '/',
                        'domain' => '', // Current domain
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ];
                    setcookie('remember_validator', $new_validator, $cookie_options_update);
                    */

                } else {
                     // User associated with token not found. Should not happen if DB is consistent.
                    clear_auth_cookies_and_db_token($pdo, $selector);
                }
            } else {
                // Validator does not match - invalid token (possible tampering or old token).
                // Clear cookies and the specific DB entry for this selector.
                clear_auth_cookies_and_db_token($pdo, $selector);
            }
        } else {
            // Selector not found in DB or token expired. Clear cookies from browser.
            clear_auth_cookies_and_db_token($pdo, null); // selector not needed if just clearing cookies
        }
    } catch (PDOException $e) {
        error_log("Auto-login (Remember Me) DB Error: " . $e->getMessage());
        clear_auth_cookies_and_db_token($pdo, null); // Clear cookies on error to prevent loop
    }
}

// Helper function to clear cookies and optionally a DB token by selector
// This can be defined here or in a separate utility file included earlier.
if (!function_exists('clear_auth_cookies_and_db_token')) {
    function clear_auth_cookies_and_db_token($pdo_conn, $selector_to_delete) {
        $cookie_options = [
            'expires' => time() - 3600, // Expire in the past
            'path' => '/',
            'domain' => '', // Current domain
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        setcookie('remember_selector', '', $cookie_options);
        setcookie('remember_validator', '', $cookie_options);

        if ($pdo_conn && $selector_to_delete) {
            try {
                $sql_delete = "DELETE FROM auth_tokens WHERE selector = :selector";
                $stmt_delete = $pdo_conn->prepare($sql_delete);
                $stmt_delete->execute([':selector' => $selector_to_delete]);
            } catch (PDOException $e) {
                error_log("Error deleting auth token by selector: " . $e->getMessage());
            }
        }
    }
}


// Define a base path for file uploads if needed
define('UPLOAD_DIR_PRODUCT', $_SERVER['DOCUMENT_ROOT'] . '/your_php_ecommerce_project/admin/assets/uploads/products/'); // Adjust path
define('UPLOAD_URL_PRODUCT', SITE_URL . '/admin/assets/uploads/products/'); // Adjust URL

// Create upload directory if it doesn't exist
if (!is_dir(UPLOAD_DIR_PRODUCT)) {
    mkdir(UPLOAD_DIR_PRODUCT, 0777, true);
}
?>