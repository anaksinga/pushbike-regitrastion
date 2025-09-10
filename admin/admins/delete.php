<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();
requireSuperAdmin();

// Get admin ID from URL
$adminId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($adminId <= 0) {
    $_SESSION['error'] = "Invalid admin ID.";
    header("Location: index.php");
    exit;
}

// Get admin data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    $_SESSION['error'] = "Admin not found.";
    header("Location: index.php");
    exit;
}

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete the admin
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        
        $_SESSION['success'] = "Admin deleted successfully.";
        header("Location: index.php");
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting admin: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Delete Admin</h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Admins
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> This action cannot be undone. This will permanently delete the admin account.
            </div>
            
            <h4>Are you sure you want to delete "<?php echo $admin['username']; ?>"?</h4>
            
            <form method="post" action="">
                <div class="d-flex justify-content-end mt-4">
                    <a href="index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-danger">Delete Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>