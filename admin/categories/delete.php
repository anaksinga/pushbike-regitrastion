<!-- File: admin/categories/delete.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();

// Get category ID from URL
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($categoryId <= 0) {
    $_SESSION['error'] = "Invalid category ID.";
    header("Location: index.php");
    exit;
}

// Get category data
$category = getCategoryById($categoryId);

if (!$category) {
    $_SESSION['error'] = "Category not found.";
    header("Location: index.php");
    exit;
}

// Check if race admin has permission to delete this category
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $category['race_id']) {
    $_SESSION['error'] = "You don't have permission to delete this category.";
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
                              WHERE r.category_id = ?");
        $stmt->execute([$categoryId]);
        
        // Delete registration details
        $stmt = $pdo->prepare("DELETE rd FROM registration_details rd 
                              JOIN registrations r ON rd.registration_id = r.id 
                              WHERE r.category_id = ?");
        $stmt->execute([$categoryId]);
        
        // Delete registrations
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        
        // Finally, delete the category
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Category deleted successfully.";
        header("Location: index.php?race_id=" . $category['race_id']);
        exit;
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
        header("Location: index.php?race_id=" . $category['race_id']);
        exit;
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Delete Category</h1>
        <a href="index.php?race_id=<?php echo $category['race_id']; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Categories
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> This action cannot be undone. This will permanently delete the category and all related registrations.
            </div>
            
            <h4>Are you sure you want to delete "<?php echo $category['name']; ?>"?</h4>
            
            <form method="post" action="">
                <div class="d-flex justify-content-end mt-4">
                    <a href="index.php?race_id=<?php echo $category['race_id']; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>