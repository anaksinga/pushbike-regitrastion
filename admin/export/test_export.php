<!-- File: admin/export/test_export.php -->
<?php
// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "You must be logged in to access this page.";
    header("Location: /admin/login.php");
    exit;
}

// Get race ID from POST or GET
$raceId = isset($_POST['race_id']) ? (int)$_POST['race_id'] : (isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0);

echo "<h1>Test Export</h1>";
echo "<p>Race ID: " . $raceId . "</p>";
echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";

if ($raceId <= 0) {
    echo "<p>Error: Invalid race ID.</p>";
    exit;
}

// Get race data
$stmt = $pdo->prepare("SELECT * FROM races WHERE id = ?");
$stmt->execute([$raceId]);
$race = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$race) {
    echo "<p>Error: Race not found.</p>";
    exit;
}

echo "<h2>Race Data:</h2>";
echo "<pre>";
print_r($race);
echo "</pre>";

// Get registrations data
$stmt = $pdo->prepare("SELECT r.*, ra.name as race_name, c.name as category_name, c.registration_fee
                      FROM registrations r
                      JOIN races ra ON r.race_id = ra.id
                      JOIN categories c ON r.category_id = c.id
                      WHERE r.race_id = ?
                      ORDER BY r.registration_date DESC");
$stmt->execute([$raceId]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Registrations Data:</h2>";
echo "<p>Total Registrations: " . count($registrations) . "</p>";

// Create filename
$filename = "registrations_" . preg_replace('/[^A-Za-z0-9\-]/', '_', $race['name']) . "_" . date('YmdHis') . ".csv";
echo "<p>Filename: " . $filename . "</p>";

// Create CSV content
$csvContent = "Registration Code,Participant Name,Email,Phone,Race,Category,Registration Fee,Registration Date,Status\n";

foreach ($registrations as $registration) {
    $csvContent .= "{$registration['registration_code']},{$registration['participant_name']},{$registration['email']},{$registration['phone']},{$registration['race_name']},{$registration['category_name']},{$registration['registration_fee']}," . date('d F Y H:i', strtotime($registration['registration_date'])) . "," . ucfirst($registration['status']) . "\n";
}

echo "<h2>CSV Content (first 500 chars):</h2>";
echo "<pre>" . htmlspecialchars(substr($csvContent, 0, 500)) . "...</pre>";

// Set headers to force download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

// Output CSV content
echo $csvContent;

exit;
?>