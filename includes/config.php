<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u899271368_pendaftaran');
define('DB_PASS', 'Didi2117');
define('DB_NAME', 'u899271368_pendaftaran');

// Application configuration
define('APP_NAME', 'Pushbike Registration');
define('APP_URL', 'https://balabilalampung.com');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Create event_photos directory if it doesn't exist
if (!file_exists(UPLOAD_PATH . '/event_photos')) {
    mkdir(UPLOAD_PATH . '/event_photos', 0755, true);
}
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Start session
session_start();

// Track current page for proper redirect after reload
if (!isset($_SESSION['current_page'])) {
    $_SESSION['current_page'] = 'dashboard';
}

// Update current page on each request
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_SESSION['current_page'] = $currentPath;

// Connect to database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']) || isset($_SESSION['super_admin_id']);
}

// Check if user is super admin
function isSuperAdmin() {
    return isset($_SESSION['super_admin_id']);
}

// Check if user is race admin
function isRaceAdmin() {
    return isset($_SESSION['admin_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /admin/login.php");
        exit;
    }
}

// Redirect if not super admin
function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header("Location: /admin/dashboard.php");
        exit;
    }
}

// Generate random registration code
function generateRegistrationCode($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}