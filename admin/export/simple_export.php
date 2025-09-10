<!-- File: admin/export/simple_export.php -->
<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
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

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=u899271368_pendaftaran", "u899271368_pendaftaran", "s3cr3t");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database connection failed.";
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
} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    header("Location: /admin/dashboard.php");
    exit;
}
?>