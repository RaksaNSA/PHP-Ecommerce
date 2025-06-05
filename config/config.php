<?php
define("SITE_NAME", "E-Commerce");
define("SITE_URL", "http://localhost/php-ecommerce");
require_once 'database.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('clear_auth_cookies_and_db_token')) {
    function clear_auth_cookies_and_db_token($pdo_conn, $selector_to_delete) {
        $cookie_options = [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
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

if (!isset($_SESSION['logged_in']) && isset($_COOKIE['remember_selector']) && isset($_COOKIE['remember_validator'])) {
    $selector = $_COOKIE['remember_selector'];
    $validator_from_cookie = $_COOKIE['remember_validator'];
    try {
        $sql = "SELECT * FROM auth_tokens WHERE selector = :selector AND expires >= NOW() LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':selector' => $selector]);
        $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($token_data) {
            $hashed_validator_from_cookie = hash('sha256', $validator_from_cookie);
            if (hash_equals($token_data['hashed_token'], $hashed_validator_from_cookie)) {
                session_regenerate_id(true);
                $user_sql = "SELECT id, name FROM user WHERE id = :user_id LIMIT 1";
                $user_stmt = $pdo->prepare($user_sql);
                $user_stmt->execute([':user_id' => $token_data['user_id']]);
                $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['name'];
                    $_SESSION['logged_in'] = true;
                } else {
                    clear_auth_cookies_and_db_token($pdo, $selector);
                }
            } else {
                clear_auth_cookies_and_db_token($pdo, $selector);
            }
        } else {
            clear_auth_cookies_and_db_token($pdo, null);
        }
    } catch (PDOException $e) {
        error_log("Auto-login (Remember Me) DB Error: " . $e->getMessage());
        clear_auth_cookies_and_db_token($pdo, null);
    }
}

define('UPLOAD_DIR_PRODUCT', $_SERVER['DOCUMENT_ROOT'] . '/your_php_ecommerce_project/admin/assets/uploads/products/');
define('UPLOAD_URL_PRODUCT', SITE_URL . '/admin/assets/uploads/products/');

if (!is_dir(UPLOAD_DIR_PRODUCT)) {
    mkdir(UPLOAD_DIR_PRODUCT, 0777, true);
}
?>