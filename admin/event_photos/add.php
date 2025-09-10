<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Add Event Photo';
requireLogin();

// Get race ID from URL
$raceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;

if ($raceId <= 0) {
    $_SESSION['error'] = "Invalid race ID.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Get race data
$race = getRaceById($raceId);

if (!$race) {
    $_SESSION['error'] = "Race not found.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Check if the admin has permission to manage this race
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $raceId) {
    $_SESSION['error'] = "You don't have permission to add photos for this race.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = sanitize($_POST['caption']);
    
    if (empty($_FILES['photo']['name'])) {
        $_SESSION['error'] = "Please select a photo to upload.";
    } else {
        try {
            // Check file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                throw new Exception("Only JPG, PNG, and GIF files are allowed.");
            }
            
            // Check file size (max 5MB)
            if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                throw new Exception("File size must be less than 5MB.");
            }
            
            // Generate unique filename
            $fileInfo = pathinfo($_FILES['photo']['name']);
            $fileExtension = strtolower($fileInfo['extension']);
            $fileName = $raceId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $filePath = UPLOAD_PATH . 'event_photos/' . $fileName;
            
            // Create directory if it doesn't exist
            if (!file_exists(UPLOAD_PATH . 'event_photos/')) {
                mkdir(UPLOAD_PATH . 'event_photos/', 0755, true);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                throw new Exception("Failed to upload photo.");
            }
            
            // Insert photo record
            $stmt = $pdo->prepare("INSERT INTO event_photos (race_id, photo_path, caption) VALUES (?, ?, ?)");
            $stmt->execute([$raceId, $fileName, $caption]);
            
            $_SESSION['success'] = "Photo added successfully.";
            header("Location: index.php?race_id=" . $raceId);
            exit;
        } catch(Exception $e) {
            $_SESSION['error'] = "Error adding photo: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Event Photo</h1>
        <a href="index.php?race_id=<?php echo $raceId; ?>" class="btn btn-outline-secondary">
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
            <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Race</label>
                    <input type="text" class="form-control" value="<?php echo $race['name']; ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label for="photo" class="form-label">Photo <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                    <div class="invalid-feedback">
                        Please select a photo.
                    </div>
                    <div class="form-text">Accepted file types: JPG, PNG, GIF. Max file size: 5MB.</div>
                </div>
                
                <div class="mb-3">
                    <label for="caption" class="form-label">Caption</label>
                    <textarea class="form-control" id="caption" name="caption" rows="3"></textarea>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php?race_id=<?php echo $raceId; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>