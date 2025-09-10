<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Manage Registrations';
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
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query for registrations
$query = "SELECT r.id, r.participant_name, r.email, r.phone, r.registration_code, r.registration_date, r.status,
                 ra.name as race_name, c.name as category_name
          FROM registrations r
          JOIN races ra ON r.race_id = ra.id
          JOIN categories c ON r.category_id = c.id
          WHERE 1=1";

$params = [];

if ($selectedRaceId > 0) {
    $query .= " AND r.race_id = ?";
    $params[] = $selectedRaceId;
}

if (!empty($statusFilter)) {
    $query .= " AND r.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY r.registration_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/admin/dashboard.php" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                
            </a>
    </div>
   <h1 class="text-center">Manage Registrations</h1>

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
                            <option value="">-- All Statuses --</option>
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
            <?php if (count($registrations) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Registration Code</th>
                            <th>Participant</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Race</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $registration): ?>
                        <tr>
                            <td><?php echo $registration['registration_code']; ?></td>
                            <td><?php echo $registration['participant_name']; ?></td>
                            <td><?php echo $registration['email']; ?></td>
                            <td><?php echo $registration['phone']; ?></td>
                            <td><?php echo $registration['race_name']; ?></td>
                            <td><?php echo $registration['category_name']; ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($registration['registration_date'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $registration['status']; ?>">
                                    <?php echo ucfirst($registration['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/admin/registrations/view.php?id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="/admin/registrations/delete.php?id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-danger btn-delete">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">No registrations found with the selected filters.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>