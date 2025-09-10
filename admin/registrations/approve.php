<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();

// Get registration ID from URL
$registrationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($registrationId <= 0) {
    $_SESSION['error'] = "Invalid registration ID.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Get registration data
$stmt = $pdo->prepare("SELECT r.*, ra.id as race_id FROM registrations r JOIN races ra ON r.race_id = ra.id WHERE r.id = ?");
$stmt->execute([$registrationId]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    $_SESSION['error'] = "Registration not found.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Check if admin has permission to approve this registration
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $registration['race_id']) {
    $_SESSION['error'] = "You don't have permission to approve this registration.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Process approval
try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Update registration status
    $stmt = $pdo->prepare("UPDATE registrations SET status = 'approved' WHERE id = ?");
    $stmt->execute([$registrationId]);
    
    // Update proof status if exists
    $stmt = $pdo->prepare("UPDATE proofs SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE registration_id = ?");
    $adminId = isSuperAdmin() ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
    $stmt->execute([$adminId, $registrationId]);
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success'] = "Registration approved successfully.";
    header("Location: /admin/dashboard.php");
    exit;
} catch(PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error approving registration: " . $e->getMessage();
    header("Location: /admin/dashboard.php");
    exit;
}