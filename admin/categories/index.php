<!-- File: admin/categories/index.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Manage Categories';
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

// Get categories
if ($selectedRaceId > 0) {
    $categories = getCategoriesByRaceId($selectedRaceId);
} else {
    $categories = [];
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        
        <a href="/admin/dashboard.php" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        <h1>Manage Categories</h1>
        <?php if ($selectedRaceId > 0): ?>
        <a href="add.php?race_id=<?php echo $selectedRaceId; ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New Category
        </a>
        <?php endif; ?>
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
                    <div class="col-md-6">
                        <label for="race_id" class="form-label">Select Race</label>
                        <select class="form-select" id="race_id" name="race_id" onchange="this.form.submit()">
                            <option value="">-- Select Race --</option>
                            <?php foreach ($races as $race): ?>
                            <option value="<?php echo $race['id']; ?>" <?php echo $selectedRaceId == $race['id'] ? 'selected' : ''; ?>>
                                <?php echo $race['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($selectedRaceId > 0): ?>
    <div class="card">
        <div class="card-body">
            <?php if (count($categories) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Age Group</th>
                            <th>Quota</th>
                            <th>Registration Fee</th>
                            <th>Registrations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <?php
                        $registrationCount = countRegistrationsByCategoryId($category['id']);
                        $quotaPercentage = ($category['quota'] > 0) ? ($registrationCount / $category['quota']) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo $category['name']; ?></td>
                            <td><?php echo $category['age_group']; ?></td>
                            <td><?php echo $category['quota']; ?></td>
                            <td><?php echo formatCurrency($category['registration_fee']); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?php echo $registrationCount; ?> / <?php echo $category['quota']; ?></span>
                                    <div class="progress flex-grow-1" style="height: 10px;">
                                        <div class="progress-bar <?php echo $quotaPercentage >= 100 ? 'bg-danger' : ($quotaPercentage >= 75 ? 'bg-warning' : 'bg-success'); ?>" 
                                             role="progressbar" style="width: <?php echo min($quotaPercentage, 100); ?>%;" 
                                             aria-valuenow="<?php echo $quotaPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">No categories found for this race. <a href="add.php?race_id=<?php echo $selectedRaceId; ?>">Add a new category</a> to get started.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-tags fs-1 text-muted mb-3"></i>
            <p class="text-muted">Please select a race to view its categories.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>