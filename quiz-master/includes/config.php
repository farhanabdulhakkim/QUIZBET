<?php
// includes/config.php
session_start();

define('DB_HOST','localhost');
define('DB_NAME','quiz_master');
define('DB_USER','root');
define('DB_PASS','root'); // Change this to your MySQL root password

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    exit("DB connection failed: " . $e->getMessage());
}

/* Helper: get current user */
function current_user() {
    return $_SESSION['user'] ?? null;
}

/* Helper: check if user is admin */
function is_admin() {
    $user = current_user();
    return $user && $user['is_admin'];
}

/* Helper: require login */
function require_login() {
    if (!current_user()) {
        header('Location: /quiz-master/public/auth/login.php');
        exit;
    }
}

/* Helper: require admin */
function require_admin() {
    if (!is_admin()) {
        header('Location: /quiz-master/public/user/dashboard.php');
        exit;
    }
}