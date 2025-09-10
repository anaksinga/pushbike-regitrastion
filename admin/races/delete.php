<!-- File: admin/races/delete.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
requireSuperAdmin();

// Get race ID from URL
$raceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($raceId <= 0) {
    $_SESSION['error'] = "Invalid race ID.";
    header("Location: index.php");
    exit;
}

// Get race data
$race = getRaceById($raceId);

if (!$race) {
    $_SESSION['error'] = "Race not found.";
    header("Location: index.php");
    exit;
}

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Delete related data first (due to foreign key constraints)
        // Delete proofs
        $stmt = $pdo->prepare("DELETE p FROM proofs p 
                              JOIN registrations r ON p.registration_id = r.id 
                              WHERE r.race_id = ?");
        $stmt->execute([$raceId]);
        
        // Delete registration details
        $stmt = $pdo->prepare("DELETE rd FROM registration_details rd 
                              JOIN registrations r ON rd.registration_id = r.id 
                              WHERE r.race_id = ?");
        $stmt->execute([$raceId]);
        
        // Delete registrations
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE race_id = ?");
        $stmt->execute([$raceId]);
        
        // Delete categories
        $stmt = $pdo->prepare("DELETE FROM categories WHERE race_id = ?");
        $stmt->execute([$raceId]);
        
        // Delete forms
        $stmt = $pdo->prepare("DELETE FROM forms WHERE race_id = ?");
        $stmt->execute([$raceId]);
        
        // Update admins to remove race assignment
        $stmt = $pdo->prepare("UPDATE admins SET race_id = NULL WHERE race_id = ?");
        $stmt->execute([$raceId]);
        
        // Finally, delete the race
        $stmt = $pdo->prepare("DELETE FROM races WHERE id = ?");
        $stmt->execute([$raceId]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Race deleted successfully.";
        header("Location: index.php");
        exit;
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting race: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Delete Race</h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Races
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> This action cannot be undone. This will permanently delete the race and all related data including categories, forms, and registrations.
            </div>
            
            <h4>Are you sure you want to delete "<?php echo $race['name']; ?>"?</h4>
            
            <form method="post" action="">
                <div class="d-flex justify-content-end mt-4">
                    <a href="index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-danger">Delete Race</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>