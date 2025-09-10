<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'View Registration';
requireLogin();

// Get registration ID from URL
$registrationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($registrationId <= 0) {
    $_SESSION['error'] = "Invalid registration ID.";
    header("Location: index.php");
    exit;
}

// Get registration data
$stmt = $pdo->prepare("SELECT r.*, ra.name as race_name, c.name as category_name, c.registration_fee, ra.whatsapp_group_link, ra.id as race_id
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

// Check if admin has permission to view this registration
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $registration['race_id']) {
    $_SESSION['error'] = "You don't have permission to view this registration.";
    header("Location: index.php");
    exit;
}

// Get registration details
$registrationDetails = getRegistrationDetails($registration['id']);

// Get proof data
$proof = getProofByRegistrationId($registration['id']);

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>View Registration</h1>
        <div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Registrations
            </a>
            <?php if (isSuperAdmin() || (isRaceAdmin() && $_SESSION['admin_race_id'] == $registration['race_id'])): ?>
            <a href="edit.php?id=<?php echo $registration['id']; ?>" class="btn btn-outline-primary ms-2">
                <i class="bi bi-pencil me-2"></i>Edit Registration
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
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
                            <th>Email:</th>
                            <td><?php echo $registration['email']; ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo $registration['phone']; ?></td>
                        </tr>
                        <tr>
                            <th>Race:</th>
                            <td><?php echo $registration['race_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td><?php echo $registration['category_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Registration Fee:</th>
                            <td><?php echo formatCurrency($registration['registration_fee']); ?></td>
                        </tr>
                        <tr>
                            <th>Registration Date:</th>
                            <td><?php echo date('d F Y, H:i', strtotime($registration['registration_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="status-badge <?php echo $registration['status']; ?>">
                                    <?php echo ucfirst($registration['status']); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h5>Additional Information</h5>
                    <?php if (count($registrationDetails) > 0): ?>
                    <table class="table table-borderless">
                        <?php foreach ($registrationDetails as $detail): ?>
                        <tr>
                            <th width="40%"><?php echo $detail['field_name']; ?>:</th>
                            <td><?php echo $detail['field_value']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php else: ?>
                    <p class="text-muted">No additional information available.</p>
                    <?php endif; ?>
                    
                    <h5 class="mt-4">Payment Proof</h5>
                    <?php if ($proof): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="/uploads/<?php echo $proof['file_path']; ?>" class="img-fluid" alt="Payment Proof">
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                <span class="status-badge <?php echo $proof['status']; ?>">
                                                    <?php echo ucfirst($proof['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Notes:</th>
                                            <td><?php echo $proof['notes'] ?: 'No notes'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Upload Date:</th>
                                            <td><?php echo date('d F Y, H:i', strtotime($proof['created_at'])); ?></td>
                                        </tr>
                                        <?php if ($proof['reviewed_at']): ?>
                                        <tr>
                                            <th>Reviewed At:</th>
                                            <td><?php echo date('d F Y, H:i', strtotime($proof['reviewed_at'])); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#proofModal">
                                            <i class="bi bi-pencil me-1"></i> Review Payment Proof
                                        </button>
                                        <a href="/uploads/<?php echo $proof['file_path']; ?>" target="_blank" class="btn btn-outline-primary ms-2">
                                            <i class="bi bi-download me-1"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No payment proof uploaded.
                        <div class="mt-2">
                            <a href="/admin/registrations/upload_proof.php?id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-upload me-1"></i> Upload Payment Proof
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-outline-secondary">Back to Registrations</a>
                <?php if ($registration['status'] === 'pending'): ?>
                <a href="/admin/registrations/approve.php?id=<?php echo $registration['id']; ?>" class="btn btn-success">
                    <i class="bi bi-check me-1"></i>Approve Registration
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Proof Review Modal -->
<?php if ($proof): ?>
<div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proofModalLabel">Review Payment Proof</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="proofReviewForm" method="post" action="/admin/proofs/review.php">
                    <input type="hidden" name="id" value="<?php echo $proof['id']; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Registration Details</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Registration Code:</th>
                                    <td><?php echo $registration['registration_code']; ?></td>
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
                        <div class="col-md-6">
                            <h6>Payment Proof</h6>
                            <div class="text-center mb-3">
                                <img src="/uploads/<?php echo $proof['file_path']; ?>" class="img-fluid" style="max-height: 200px;" alt="Payment Proof">
                            </div>
                            <div class="text-center">
                                <a href="/uploads/<?php echo $proof['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i> Download Full Size
                                </a>
                            </div>
                        </div>
                    </div>
                    
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitProofReview()">Update Status</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function submitProofReview() {
    document.getElementById('proofReviewForm').submit();
}

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