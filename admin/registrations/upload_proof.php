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
$stmt = $pdo->prepare("SELECT r.*, ra.id as race_id, ra.name as race_name, c.name as category_name 
                      FROM registrations r 
                      JOIN races ra ON r.race_id = ra.id 
                      JOIN categories c ON r.category_id = c.id 
                      WHERE r.id = ?");
$stmt->execute([$registrationId]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    $_SESSION['error'] = "Registration not found.";
    header("Location: index.php");
    exit;
}

// Check if admin has permission to upload proof for this registration
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $registration['race_id']) {
    $_SESSION['error'] = "You don't have permission to upload proof for this registration.";
    header("Location: index.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['proof']['name'])) {
        $_SESSION['error'] = "Please select a proof file to upload.";
    } else {
        try {
            // Check file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!in_array($_FILES['proof']['type'], $allowedTypes)) {
                throw new Exception("Only JPG, PNG, and PDF files are allowed.");
            }
            
            // Check file size (max 2MB)
            if ($_FILES['proof']['size'] > 2 * 1024 * 1024) {
                throw new Exception("File size must be less than 2MB.");
            }
            
            // Generate unique filename
            $fileInfo = pathinfo($_FILES['proof']['name']);
            $fileExtension = strtolower($fileInfo['extension']);
            $fileName = $registration['registration_code'] . '_' . time() . '.' . $fileExtension;
            $filePath = UPLOAD_PATH . $fileName;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['proof']['tmp_name'], $filePath)) {
                throw new Exception("Failed to upload proof.");
            }
            
            // Check if proof already exists
            $proof = getProofByRegistrationId($registrationId);
            
            if ($proof) {
                // Update existing proof
                $stmt = $pdo->prepare("UPDATE proofs SET file_path = ?, status = 'pending', notes = NULL, reviewed_by = NULL, reviewed_at = NULL WHERE registration_id = ?");
                $stmt->execute([$fileName, $registrationId]);
            } else {
                // Insert new proof
                $stmt = $pdo->prepare("INSERT INTO proofs (registration_id, file_path) VALUES (?, ?)");
                $stmt->execute([$registrationId, $fileName]);
            }
            
            // Update registration status to pending
            $stmt = $pdo->prepare("UPDATE registrations SET status = 'pending' WHERE id = ?");
            $stmt->execute([$registrationId]);
            
            $_SESSION['success'] = "Payment proof uploaded successfully.";
            header("Location: view.php?id=" . $registrationId);
            exit;
        } catch(Exception $e) {
            $_SESSION['error'] = "Error uploading proof: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Upload Payment Proof';
include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Upload Payment Proof</h1>
        <a href="view.php?id=<?php echo $registrationId; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Registration
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
            <div class="mb-3">
                <h5>Registration Details</h5>
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Registration Code:</th>
                        <td><strong><?php echo $registration['registration_code']; ?></strong></td>
                    </tr>
                    <tr>
                        <th>Participant Name:</th>
                        <td><?php echo $registration['participant_name']; ?></td>
                    </tr>
                    <tr>
                        <th>Race:</th>
                        <td><?php echo $registration['race_name']; ?></td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td><?php echo $registration['category_name']; ?></td>
                    </tr>
                </table>
            </div>
            
            <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="proof" class="form-label">Payment Proof <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="proof" name="proof" accept="image/*,.pdf" required>
                    <div class="invalid-feedback">
                        Please select a proof file.
                    </div>
                    <div class="form-text">Accepted file types: JPG, PNG, PDF. Max file size: 2MB.</div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="view.php?id=<?php echo $registrationId; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Upload Proof</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle form validation
(function() {
    'use strict';
    
    var forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php include '../../includes/footer.php'; ?>