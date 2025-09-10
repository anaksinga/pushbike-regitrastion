<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Edit Event Photo';
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

// Get race data
$race = getRaceById($photo['race_id']);

// Check if the admin has permission to manage this race
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $photo['race_id']) {
    $_SESSION['error'] = "You don't have permission to edit this photo.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = sanitize($_POST['caption']);
    
    try {
        // Update photo record
        $stmt = $pdo->prepare("UPDATE event_photos SET caption = ? WHERE id = ?");
        $stmt->execute([$caption, $photoId]);
        
        $_SESSION['success'] = "Photo updated successfully.";
        header("Location: index.php?race_id=" . $photo['race_id']);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating photo: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Event Photo</h1>
        <a href="index.php?race_id=<?php echo $photo['race_id']; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Photos
        </a>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="post" action="" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Race</label>
                            <input type="text" class="form-control" value="<?php echo $race['name']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <img src="/uploads/event_photos/<?php echo $photo['photo_path']; ?>" class="img-thumbnail" alt="Event Photo">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="caption" class="form-label">Caption</label>
                    <textarea class="form-control" id="caption" name="caption" rows="3"><?php echo $photo['caption']; ?></textarea>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php?race_id=<?php echo $photo['race_id']; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>