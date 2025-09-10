<!-- File: admin/export/minimal_export.php -->
<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    die("You must be logged in");
}

// Get race ID
$raceId = isset($_POST['race_id']) ? (int)$_POST['race_id'] : 0;
if ($raceId <= 0) {
    die("Invalid race ID");
}

// Database connection (replace with your actual credentials)
$host = 'localhost';
$dbname = 'u899271368_pendaftaran';
$user = 'u899271368_pendaftaran';
$pass = 'Didi2117';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get race data
    $stmt = $pdo->prepare("SELECT * FROM races WHERE id = ?");
    $stmt->execute([$raceId]);
    $race = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$race) {
        die("Race not found");
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
    
    // Set headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    // Output CSV
    $output = fopen('php://output', 'w');
    
    // Headers
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
    
    // Data rows
    foreach ($registrations as $row) {
        fputcsv($output, array(
            $row['registration_code'],
            $row['participant_name'],
            $row['email'],
            $row['phone'],
            $row['race_name'],
            $row['category_name'],
            $row['registration_fee'],
            date('d F Y H:i', strtotime($row['registration_date'])),
            ucfirst($row['status'])
        ));
    }
    
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>