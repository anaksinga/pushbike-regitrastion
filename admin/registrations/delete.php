<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();

// Get registration ID from URL
$registrationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($registrationId <= 0) {
    $_SESSION['error'] = "Invalid registration ID.";
    header("Location: index.php");
    exit;
}

// Get registration data
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->execute([$registrationId]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    $_SESSION['error'] = "Registration not found.";
    header("Location: index.php");
    exit;
}

// Check if admin has permission to delete this registration
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $registration['race_id']) {
    $_SESSION['error'] = "You don't have permission to delete this registration.";
    header("Location: index.php");
    exit;
}

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Delete proof
        $stmt = $pdo->prepare("DELETE FROM proofs WHERE registration_id = ?");
        $stmt->execute([$registrationId]);
        
        // Delete registration details
        $stmt = $pdo->prepare("DELETE FROM registration_details WHERE registration_id = ?");
        $stmt->execute([$registrationId]);
        
        // Delete registration
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
        $stmt->execute([$registrationId]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Registration deleted successfully.";
        header("Location: index.php");
        exit;
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting registration: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Delete Registration</h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Registrations
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> This action cannot be undone. This will permanently delete the registration and all related data.
            </div>
            
            <h4>Are you sure you want to delete the registration for "<?php echo $registration['participant_name']; ?>"?</h4>
            
            <form method="post" action="">
                <div class="d-flex justify-content-end mt-4">
                    <a href="index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-danger">Delete Registration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>