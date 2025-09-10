<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Review Payment Proofs';
requireLogin();

// Get races for filter
if (isSuperAdmin()) {
    $races = getAllRaces();
} else {
    $raceId = $_SESSION['admin_race_id'];
    $races = [$raceId => getRaceById($raceId)];
}

// Get selected race ID from URL
$selectedRaceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;

// If no race selected and user is race admin, use their assigned race
if ($selectedRaceId <= 0 && isRaceAdmin()) {
    $selectedRaceId = $_SESSION['admin_race_id'];
}

// Get status filter
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : 'pending';

// Build query for proofs
$query = "SELECT p.id, p.file_path, p.status, p.created_at, 
                 r.id as registration_id, r.participant_name, r.registration_code,
                 ra.name as race_name, c.name as category_name
          FROM proofs p
          JOIN registrations r ON p.registration_id = r.id
          JOIN races ra ON r.race_id = ra.id
          JOIN categories c ON r.category_id = c.id
          WHERE 1=1";

$params = [];

if ($selectedRaceId > 0) {
    $query .= " AND r.race_id = ?";
    $params[] = $selectedRaceId;
}

if (!empty($statusFilter)) {
    $query .= " AND p.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$proofs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/admin/dashboard.php" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        <h1>Review Payment Proofs</h1>
        
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
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="">
                <div class="row">
                    <div class="col-md-4">
                        <label for="race_id" class="form-label">Select Race</label>
                        <select class="form-select" id="race_id" name="race_id" onchange="this.form.submit()">
                            <option value="">-- All Races --</option>
                            <?php foreach ($races as $race): ?>
                            <option value="<?php echo $race['id']; ?>" <?php echo $selectedRaceId == $race['id'] ? 'selected' : ''; ?>>
                                <?php echo $race['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (count($proofs) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Registration Code</th>
                            <th>Participant</th>
                            <th>Race</th>
                            <th>Category</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proofs as $proof): ?>
                        <tr>
                            <td><?php echo $proof['registration_code']; ?></td>
                            <td><?php echo $proof['participant_name']; ?></td>
                            <td><?php echo $proof['race_name']; ?></td>
                            <td><?php echo $proof['category_name']; ?></td>
                            <td>
                                <a href="/uploads/<?php echo $proof['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $proof['status']; ?>">
                                    <?php echo ucfirst($proof['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y, H:i', strtotime($proof['created_at'])); ?></td>
                            <td>
                                <a href="review.php?id=<?php echo $proof['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Review
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">No payment proofs found with the selected filters.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>