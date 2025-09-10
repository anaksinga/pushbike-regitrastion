<!-- File: admin/races/index.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Manage Races';
requireLogin();
requireSuperAdmin();
// Get selected race ID from URL
$selectedRaceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;
// Get all races
$races = getAllRaces();
include '../../includes/header.php';
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
         <a href="/admin/dashboard.php" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        <h1>Manage Races</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New Race
        </a>
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
                        <label for="race_id" class="form-label">Filter by Race</label>
                        <select class="form-select" id="race_id" name="race_id" onchange="this.form.submit()">
                            <option value="">-- All Races --</option>
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
    
    <div class="card">
        <div class="card-body">
            <?php if (count($races) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Nomor Rekening</th>
                            <th>Categories</th>
                            <th>Registrations</th>
                            <th>Photos</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($races as $race): ?>
                        <?php
                        // Get categories count for this race
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM categories WHERE race_id = ?");
                        $stmt->execute([$race['id']]);
                        $categoriesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                        
                        // Get registrations count for this race
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM registrations WHERE race_id = ?");
                        $stmt->execute([$race['id']]);
                        $registrationsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                        
                        // Get photos count for this race
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM event_photos WHERE race_id = ?");
                        $stmt->execute([$race['id']]);
                        $photosCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($race['image'])): ?>
                                <img src="/uploads/races/<?php echo $race['image']; ?>" alt="<?php echo $race['name']; ?>" 
                                     class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-light text-center p-2" style="width: 80px; height: 60px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $race['id']; ?></td>
                            <td><?php echo $race['name']; ?></td>
                            <td><?php echo $race['description'] ?: 'No description'; ?></td>
                            <td><?php echo $race['location']; ?></td>
                            <td><?php echo formatDate($race['event_date']); ?></td>
                            <td><?php echo $race['nomor_rekening'] ?: '-'; ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo $categoriesCount; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-success"><?php echo $registrationsCount; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $photosCount; ?></span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="view.php?id=<?php echo $race['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $race['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="categories/index.php?race_id=<?php echo $race['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-tags"></i>
                                    </a>
                                    <a href="/admin/event_photos/index.php?race_id=<?php echo $race['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-images"></i>
                                    </a>
                                    
                                    <!-- Tombol untuk minimal export di admin/races/index.php -->
                                    <form method="post" action="/admin/export/minimal_export.php" style="display: inline;">
                                        <input type="hidden" name="race_id" value="<?php echo $race['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <i class="bi bi-file-earmark-excel"></i> Export
                                        </button>
                                    </form>
                                    <a href="delete.php?id=<?php echo $race['id']; ?>" class="btn btn-sm btn-outline-danger btn-delete">
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
            <p class="text-muted">No races found. <a href="add.php">Add a new race</a> to get started.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>