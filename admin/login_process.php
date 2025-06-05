<?php
// login_process.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include '../config/database.php';
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

function store_auth_token($pdo, $user_id, $selector, $hashed_validator, $expires_timestamp) {
    try {
        // Clear any old tokens for this user
        $sql_delete_old = "DELETE FROM auth_tokens WHERE user_id = :user_id";
        $stmt_delete_old = $pdo->prepare($sql_delete_old);
        $stmt_delete_old->execute([':user_id' => $user_id]);

        // Insert new token
        $sql_insert = "INSERT INTO auth_tokens (user_id, selector, hashed_token, expires) VALUES (:user_id, :selector, :hashed_token, :expires)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([
            ':user_id' => $user_id,
            ':selector' => $selector,
            ':hashed_token' => $hashed_validator,
            ':expires' => date('Y-m-d H:i:s', $expires_timestamp)
        ]);
        return true;
    } catch (PDOException $e) {
        // Log error: error_log("Error storing auth token: " . $e->getMessage());
        return false;
    }
}

function clear_auth_cookies() {
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
}

function clear_user_auth_tokens($pdo, $user_id) {
     try {
        $sql = "DELETE FROM auth_tokens WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
    } catch (PDOException $e) {

    }
}


$submitted_username = $_POST['username'] ?? '';
$submitted_password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';
if (empty($submitted_username) || empty($submitted_password)) {
    exit();
}

$username_is_valid = false;
$password_is_correct = false;
$user_id_from_database = null;
$username_from_database = null;

try {
    $sql = "SELECT id, username, password FROM users WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $submitted_username, PDO::PARAM_STR);
    $stmt->execute();
    $users = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($users) {
        if (password_verify($submitted_password, $users['password'])) {
            $password_is_correct = true;
            $username_is_valid = true;
            $user_id_from_database = $users['id'];
            $username_from_database = $users['username'];
        }
    }
} catch (PDOException $e) {
    $_SESSION['login_error'] = "Database error. Please try again later.";
    // error_log("Login DB Error: " . $e->getMessage());
    header("Location: login.php");
    exit();
}
if ($username_is_valid && $password_is_correct) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id_from_database;
    $_SESSION['username'] = $username_from_database;
    $_SESSION['logged_in'] = true;

    if ($remember_me) {
        $selector = generate_token(16);
        $validator = generate_token(32);
        $hashed_validator = hash('sha256', $validator);
        $expires_timestamp = time() + (86400 * 30);

        if (store_auth_token($pdo, $user_id_from_database, $selector, $hashed_validator, $expires_timestamp)) {
            $cookie_options = [
                'expires' => $expires_timestamp,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ];
            setcookie('remember_selector', $selector, $cookie_options);
            setcookie('remember_validator', $validator, $cookie_options); // Store the raw validator
        } else {
           
        }
    } else {
        clear_auth_cookies();
        clear_user_auth_tokens($pdo, $user_id_from_database);
    }
    header("Location: dashboard.php");
    exit();
} else {
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: login.php");
    exit();
}
?>