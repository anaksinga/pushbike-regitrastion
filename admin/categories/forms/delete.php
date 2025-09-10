<!-- File: admin/forms/delete.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();

// Get form field ID from URL
$fieldId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($fieldId <= 0) {
    $_SESSION['error'] = "Invalid form field ID.";
    header("Location: index.php");
    exit;
}

// Get form field data
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$fieldId]);
$field = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$field) {
    $_SESSION['error'] = "Form field not found.";
    header("Location: index.php");
    exit;
}

// Check if race admin has permission to delete this form field
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $field['race_id']) {
    $_SESSION['error'] = "You don't have permission to delete this form field.";
    header("Location: index.php");
    exit;
}

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Delete registration details
        $stmt = $pdo->prepare("DELETE FROM registration_details WHERE form_field_id = ?");
        $stmt->execute([$fieldId]);
        
        // Delete the form field
        $stmt = $pdo->prepare("DELETE FROM forms WHERE id = ?");
        $stmt->execute([$fieldId]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Form field deleted successfully.";
        header("Location: index.php?race_id=" . $field['race_id']);
        exit;
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting form field: " . $e->getMessage();
        header("Location: index.php?race_id=" . $field['race_id']);
        exit;
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Delete Form Field</h1>
        <a href="index.php?race_id=<?php echo $field['race_id']; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Form Fields
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> This action cannot be undone. This will permanently delete the form field and all related data.
            </div>
            
            <h4>Are you sure you want to delete "<?php echo $field['field_name']; ?>"?</h4>
            
            <form method="post" action="">
                <div class="d-flex justify-content-end mt-4">
                    <a href="index.php?race_id=<?php echo $field['race_id']; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-danger">Delete Form Field</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>