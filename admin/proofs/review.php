<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Review Payment Proof';
requireLogin();

// Get proof ID from URL
$proofId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($proofId <= 0) {
    $_SESSION['error'] = "Invalid proof ID.";
    header("Location: index.php");
    exit;
}

// Get proof data with registration details
$stmt = $pdo->prepare("SELECT p.*, r.id as registration_id, r.participant_name, r.email, r.phone, r.registration_code, 
                              ra.name as race_name, c.name as category_name, ra.id as race_id
                      FROM proofs p
                      JOIN registrations r ON p.registration_id = r.id
                      JOIN races ra ON r.race_id = ra.id
                      JOIN categories c ON r.category_id = c.id
                      WHERE p.id = ?");
$stmt->execute([$proofId]);
$proof = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proof) {
    $_SESSION['error'] = "Proof not found.";
    header("Location: index.php");
    exit;
}

// Check if race admin has permission to review this proof
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $proof['race_id']) {
    $_SESSION['error'] = "You don't have permission to review this proof.";
    header("Location: index.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);
    
    if (empty($status)) {
        $_SESSION['error'] = "Please select a status.";
    } else {
        try {
            // Update proof status
            $stmt = $pdo->prepare("UPDATE proofs SET status = ?, notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
            $adminId = isSuperAdmin() ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
            $stmt->execute([$status, $notes, $adminId, $proofId]);
            
            // Update registration status
            $stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $proof['registration_id']]);
            
            $_SESSION['success'] = "Payment proof status updated successfully.";
            header("Location: index.php?race_id=" . $proof['race_id']);
            exit;
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error updating proof status: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Review Payment Proof</h1>
        <a href="index.php?race_id=<?php echo $proof['race_id']; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Payment Proofs
        </a>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Registration Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Registration Code:</th>
                            <td><strong><?php echo $proof['registration_code']; ?></strong></td>
                        </tr>
                        <tr>
                            <th>Participant Name:</th>
                            <td><?php echo $proof['participant_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo $proof['email']; ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo $proof['phone']; ?></td>
                        </tr>
                        <tr>
                            <th>Race:</th>
                            <td><?php echo $proof['race_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td><?php echo $proof['category_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Current Status:</th>
                            <td>
                                <span class="status-badge <?php echo $proof['status']; ?>">
                                    <?php echo ucfirst($proof['status']); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Proof</h5>
                </div>
                <div class="card-body text-center">
                    <img src="/uploads/<?php echo $proof['file_path']; ?>" class="img-fluid mb-3" alt="Payment Proof">
                    <div>
                        <a href="/uploads/<?php echo $proof['file_path']; ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-download me-1"></i> Download Full Size
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Update Status</h5>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="pending" <?php echo $proof['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $proof['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $proof['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <div class="invalid-feedback">
                        Please select a status.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo $proof['notes']; ?></textarea>
                    <div class="form-text">Add any notes about this payment proof (optional).</div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php?race_id=<?php echo $proof['race_id']; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>