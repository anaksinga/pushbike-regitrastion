<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireLogin();

// Get photo ID from URL
$photoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($photoId <= 0) {
    $_SESSION['error'] = "Invalid photo ID.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Get photo data
$stmt = $pdo->prepare("SELECT * FROM event_photos WHERE id = ?");
$stmt->execute([$photoId]);
$photo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$photo) {
    $_SESSION['error'] = "Photo not found.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Get race ID from URL
$raceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : $photo['race_id'];

// Check if the admin has permission to manage this race
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $photo['race_id']) {
    $_SESSION['error'] = "You don't have permission to delete this photo.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete photo file
        $filePath = UPLOAD_PATH . 'event_photos/' . $photo['photo_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete photo record
        $stmt = $pdo->prepare("DELETE FROM event_photos WHERE id = ?");
        $stmt->execute([$photoId]);
        
        $_SESSION['success'] = "Photo deleted successfully.";
        header("Location: index.php?race_id=" . $raceId);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting photo: " . $e->getMessage();
        header("Location: index.php?race_id=" . $raceId);
        exit;
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Delete Event Photo</h1>
        <a href="index.php?race_id=<?php echo $raceId; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Photos
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> This action cannot be undone. This will permanently delete the photo.
            </div>
            
            <div class="text-center mb-4">
                <img src="/uploads/event_photos/<?php echo $photo['photo_path']; ?>" class="img-fluid" style="max-height: 300px;" alt="Event Photo">
                <p class="mt-2"><?php echo $photo['caption']; ?></p>
            </div>
            
            <h4 class="text-center">Are you sure you want to delete this photo?</h4>
            
            <form method="post" action="">
                <div class="d-flex justify-content-end mt-4">
                    <a href="index.php?race_id=<?php echo $raceId; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-danger">Delete Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>