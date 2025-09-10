<!-- File: user/success.php -->
<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
$pageTitle = 'Registration Successful';

// Get registration code from URL
$registrationCode = isset($_GET['registration_code']) ? sanitize($_GET['registration_code']) : '';

if (empty($registrationCode)) {
    header("Location: races.php");
    exit;
}

// Get registration data
$stmt = $pdo->prepare("SELECT r.*, ra.name as race_name, c.name as category_name, ra.whatsapp_group_link
                      FROM registrations r
                      JOIN races ra ON r.race_id = ra.id
                      JOIN categories c ON r.category_id = c.id
                      WHERE r.registration_code = ?");
$stmt->execute([$registrationCode]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    header("Location: races.php");
    exit;
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="success-container">
                <div class="icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h1>Registration Successful!</h1>
                <p>Thank you for registering for the <strong><?php echo $registration['race_name']; ?></strong>.</p>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Registration Details</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Registration Code:</th>
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
                            <tr>
                                <th>Email:</th>
                                <td><?php echo $registration['email']; ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?php echo $registration['phone']; ?></td>
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
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Your registration is currently <strong><?php echo $registration['status']; ?></strong>. 
                            <?php if ($registration['status'] === 'pending'): ?>
                            Please wait for the admin to verify your payment proof.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($registration['whatsapp_group_link'])): ?>
                <div class="alert alert-success">
                    <h5 class="alert-heading">Join Our WhatsApp Group!</h5>
                    <p>Join our WhatsApp group to get the latest updates about the race and connect with other participants.</p>
                    <hr>
                    <p class="mb-0">
                        <a href="<?php echo $registration['whatsapp_group_link']; ?>" target="_blank" class="whatsapp-link">
                            <i class="bi bi-whatsapp me-2"></i>Join WhatsApp Group
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="races.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Races
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>