<!-- File: admin/export/direct_export.php -->
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Try to include required files with error handling
try {
    // Define base path
    $basePath = dirname(__DIR__, 2);
    
    // Include config file
    if (file_exists($basePath . '/includes/config.php')) {
        require_once $basePath . '/includes/config.php';
    } else {
        throw new Exception("Config file not found");
    }
    
    // Include functions file
    if (file_exists($basePath . '/includes/functions.php')) {
        require_once $basePath . '/includes/functions.php';
    } else {
        throw new Exception("Functions file not found");
    }
} catch (Exception $e) {
    // Log error and redirect
    error_log("Export error: " . $e->getMessage());
    $_SESSION['error'] = "System error occurred. Please try again later.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Check if user is logged in
if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    $_SESSION['error'] = "You must be logged in to access this page.";
    header("Location: /admin/login.php");
    exit;
}

// Get race ID from POST
$raceId = isset($_POST['race_id']) ? (int)$_POST['race_id'] : 0;

if ($raceId <= 0) {
    $_SESSION['error'] = "Invalid race ID.";
    header("Location: /admin/dashboard.php");
    exit;
}

try {
    // Get race data
    $stmt = $pdo->prepare("SELECT * FROM races WHERE id = ?");
    $stmt->execute([$raceId]);
    $race = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$race) {
        $_SESSION['error'] = "Race not found.";
        header("Location: /admin/dashboard.php");
        exit;
    }

    // Get registrations data
    $stmt = $pdo->prepare("SELECT r.*, ra.name as race_name, c.name as category_name, c.registration_fee
                          FROM registrations r
                          JOIN races ra ON r.race_id = ra.id
                          JOIN categories c ON r.category_id = c.id
                          WHERE r.race_id = ?
                          ORDER BY r.registration_date DESC");
    $stmt->execute([$raceId]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create filename
    $filename = "registrations_" . preg_replace('/[^A-Za-z0-9\-]/', '_', $race['name']) . "_" . date('YmdHis') . ".csv";

    // Clear any output that might have already been generated
    if (ob_get_length()) {
        ob_clean();
    }

    // Set headers to force download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM to fix encoding issues in Excel
    fputs($output, "\xEF\xBB\xBF");

    // Add CSV headers
    fputcsv($output, array(
        'Registration Code',
        'Participant Name',
        'Email',
        'Phone',
        'Race',
        'Category',
        'Registration Fee',
        'Registration Date',
        'Status'
    ));

    // Add data rows
    foreach ($registrations as $registration) {
        fputcsv($output, array(
            $registration['registration_code'],
            $registration['participant_name'],
            $registration['email'],
            $registration['phone'],
            $registration['race_name'],
            $registration['category_name'],
            $registration['registration_fee'],
            date('d F Y H:i', strtotime($registration['registration_date'])),
            ucfirst($registration['status'])
        ));
    }

    // Close the file pointer
    fclose($output);

    // Stop execution to prevent any additional output
    exit;
} catch (PDOException $e) {
    // Log database error
    error_log("Database error in export: " . $e->getMessage());
    $_SESSION['error'] = "Database error occurred. Please try again later.";
    header("Location: /admin/dashboard.php");
    exit;
} catch (Exception $e) {
    // Log general error
    error_log("General error in export: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header("Location: /admin/dashboard.php");
    exit;
}
?>