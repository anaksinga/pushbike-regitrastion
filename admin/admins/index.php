<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Manage Admins';
requireLogin();
requireSuperAdmin();

// Get all admins with their race information
$stmt = $pdo->query("SELECT a.*, r.name as race_name FROM admins a LEFT JOIN races r ON a.race_id = r.id ORDER BY a.created_at DESC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all races for the dropdown
$races = getAllRaces();

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
         <a href="/admin/dashboard.php" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        <h1>Manage Admins</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New Admin
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
    
    <div class="card">
        <div class="card-body">
            <?php if (count($admins) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Race</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo $admin['id']; ?></td>
                            <td><?php echo $admin['username']; ?></td>
                            <td><?php echo $admin['race_name'] ? $admin['race_name'] : 'Not Assigned'; ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($admin['created_at'])); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="edit.php?id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-outline-danger btn-delete">
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
            <p class="text-muted">No admins found. <a href="add.php">Add a new admin</a> to get started.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>