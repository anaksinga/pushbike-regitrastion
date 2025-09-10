<!-- File: admin/export/debug_export.php -->
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Export</h1>";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Session Status</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>POST Data</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Try to include required files
try {
    $basePath = dirname(__DIR__, 2);
    echo "<p>Base path: $basePath</p>";
    
    if (file_exists($basePath . '/includes/config.php')) {
        echo "<p>Config file exists</p>";
        require_once $basePath . '/includes/config.php';
    } else {
        echo "<p>Config file NOT found</p>";
    }
    
    if (file_exists($basePath . '/includes/functions.php')) {
        echo "<p>Functions file exists</p>";
        require_once $basePath . '/includes/functions.php';
    } else {
        echo "<p>Functions file NOT found</p>";
    }
} catch (Exception $e) {
    echo "<p>Error including files: " . $e->getMessage() . "</p>";
}

// Check if user is logged in
if (function_exists('isLoggedIn')) {
    if (isLoggedIn()) {
        echo "<p>User is logged in</p>";
    } else {
        echo "<p>User is NOT logged in</p>";
    }
} else {
    echo "<p>isLoggedIn function does not exist</p>";
}

// Get race ID from POST
$raceId = isset($_POST['race_id']) ? (int)$_POST['race_id'] : 0;
echo "<p>Race ID: $raceId</p>";

if ($raceId <= 0) {
    echo "<p>Invalid race ID</p>";
    exit;
}

try {
    // Get race data
    $stmt = $pdo->prepare("SELECT * FROM races WHERE id = ?");
    $stmt->execute([$raceId]);
    $race = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$race) {
        echo "<p>Race not found</p>";
        exit;
    }

    echo "<h2>Race Data</h2>";
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

    echo "<h2>Registrations Data</h2>";
    echo "<p>Total registrations: " . count($registrations) . "</p>";
    
    if (count($registrations) > 0) {
        echo "<pre>";
        print_r($registrations[0]); // Show first registration as sample
        echo "</pre>";
    }

    // Create filename
    $filename = "registrations_" . preg_replace('/[^A-Za-z0-9\-]/', '_', $race['name']) . "_" . date('YmdHis') . ".csv";
    echo "<p>Filename: $filename</p>";

    // Create CSV content
    $csvContent = "Registration Code,Participant Name,Email,Phone,Race,Category,Registration Fee,Registration Date,Status\n";

    foreach ($registrations as $registration) {
        $csvContent .= "{$registration['registration_code']},{$registration['participant_name']},{$registration['email']},{$registration['phone']},{$registration['race_name']},{$registration['category_name']},{$registration['registration_fee']}," . date('d F Y H:i', strtotime($registration['registration_date'])) . "," . ucfirst($registration['status']) . "\n";
    }

    echo "<h2>CSV Content (first 200 chars)</h2>";
    echo "<pre>" . htmlspecialchars(substr($csvContent, 0, 200)) . "...</pre>";

    // Set headers to force download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    // Output CSV content
    echo $csvContent;

    exit;
} catch (PDOException $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p>General error: " . $e->getMessage() . "</p>";
}
?>