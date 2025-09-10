<!-- File: admin/races/edit.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Edit Race';
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
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $location = sanitize($_POST['location']);
    $eventDate = sanitize($_POST['event_date']);
    $whatsappGroupLink = sanitize($_POST['whatsapp_group_link']);
    $nomorRekening = sanitize($_POST['nomor_rekening']);
    
    if (empty($name) || empty($location) || empty($eventDate)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        try {
            // Handle image upload
            $imageName = $race['image']; // Keep existing image by default
            if (isset($_FILES['race_image']) && $_FILES['race_image']['error'] === UPLOAD_ERR_OK) {
                $fileInfo = pathinfo($_FILES['race_image']['name']);
                $fileExtension = strtolower($fileInfo['extension']);
                
                // Validate file extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Invalid image type. Only JPG, PNG, and GIF files are allowed.");
                }
                
                // Validate file size (max 2MB)
                if ($_FILES['race_image']['size'] > 2 * 1024 * 1024) {
                    throw new Exception("Image size must be less than 2MB.");
                }
                
                // Create upload directory if it doesn't exist
                if (!file_exists(UPLOAD_PATH . 'races/')) {
                    mkdir(UPLOAD_PATH . 'races/', 0755, true);
                }
                
                // Generate unique filename
                $imageName = 'race_' . time() . '.' . $fileExtension;
                $filePath = UPLOAD_PATH . 'races/' . $imageName;
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['race_image']['tmp_name'], $filePath)) {
                    throw new Exception("Failed to upload race image.");
                }
                
                // Delete old image if exists
                if (!empty($race['image']) && file_exists(UPLOAD_PATH . 'races/' . $race['image'])) {
                    unlink(UPLOAD_PATH . 'races/' . $race['image']);
                }
            }
            
            $stmt = $pdo->prepare("UPDATE races SET name = ?, description = ?, location = ?, event_date = ?, whatsapp_group_link = ?, nomor_rekening = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $description, $location, $eventDate, $whatsappGroupLink, $nomorRekening, $imageName, $raceId]);
            
            $_SESSION['success'] = "Race updated successfully.";
            header("Location: index.php");
            exit;
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error updating race: " . $e->getMessage();
        } catch(Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
}
include '../../includes/header.php';
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Race</h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Races
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
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Race Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $race['name']; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a race name.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo $race['location']; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a location.
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="event_date" class="form-label">Event Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo $race['event_date']; ?>" required>
                        <div class="invalid-feedback">
                            Please provide an event date.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
                        <input type="text" class="form-control" id="nomor_rekening" name="nomor_rekening" value="<?php echo $race['nomor_rekening']; ?>" placeholder="Contoh: BCA 1234567890">
                        <div class="form-text">Nomor rekening untuk pembayaran pendaftaran</div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="whatsapp_group_link" class="form-label">WhatsApp Group Link</label>
                        <input type="text" class="form-control" id="whatsapp_group_link" name="whatsapp_group_link" value="<?php echo $race['whatsapp_group_link']; ?>" placeholder="https://chat.whatsapp.com/...">
                        <div class="form-text">This link will be shown to participants after successful registration.</div>
                    </div>
                    <div class="col-md-6">
                        <!-- Kolom kosong untuk menjaga layout -->
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo $race['description']; ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="race_image" class="form-label">Race Image</label>
                    <?php if (!empty($race['image'])): ?>
                    <div class="mb-2">
                        <img src="/uploads/races/<?php echo $race['image']; ?>" alt="<?php echo $race['name']; ?>" 
                             class="img-thumbnail" style="max-width: 300px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="race_image" name="race_image" 
                           accept=".jpg,.jpeg,.png,.gif">
                    <div class="form-text">
                        Accepted file types: JPG, PNG, GIF. Max file size: 2MB.
                        <br>Recommended dimensions: 1200x600 pixels.
                        <?php if (!empty($race['image'])): ?>
                        <br>Leave empty to keep current image.
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Race</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>