<!-- File: admin/export/simple_export.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();

// Get selected race ID from URL
$selectedRaceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;

if ($selectedRaceId <= 0) {
    $_SESSION['error'] = "Please select a race to export.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Get race data
$race = getRaceById($selectedRaceId);
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
$stmt->execute([$selectedRaceId]);
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
?>