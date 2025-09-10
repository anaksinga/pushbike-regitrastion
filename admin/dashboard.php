<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
$pageTitle = 'Admin Dashboard';
requireLogin();

// Get dashboard data
if (isSuperAdmin()) {
    // Super admin can see all data
    $racesCount = $pdo->query("SELECT COUNT(*) as count FROM races")->fetch(PDO::FETCH_ASSOC)['count'];
    $categoriesCount = $pdo->query("SELECT COUNT(*) as count FROM categories")->fetch(PDO::FETCH_ASSOC)['count'];
    $registrationsCount = $pdo->query("SELECT COUNT(*) as count FROM registrations")->fetch(PDO::FETCH_ASSOC)['count'];
    $pendingProofsCount = $pdo->query("SELECT COUNT(*) as count FROM proofs WHERE status = 'pending'")->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get admin count
    $adminsCount = $pdo->query("SELECT COUNT(*) as count FROM admins")->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get event photos count
    $eventPhotosCount = $pdo->query("SELECT COUNT(*) as count FROM event_photos")->fetch(PDO::FETCH_ASSOC)['count'];
} else {
    // Race admin can only see data for their race
    $raceId = $_SESSION['admin_race_id'];
    $racesCount = 1;
    $categoriesCount = $pdo->prepare("SELECT COUNT(*) as count FROM categories WHERE race_id = ?");
    $categoriesCount->execute([$raceId]);
    $categoriesCount = $categoriesCount->fetch(PDO::FETCH_ASSOC)['count'];
    
    $registrationsCount = $pdo->prepare("SELECT COUNT(*) as count FROM registrations WHERE race_id = ?");
    $registrationsCount->execute([$raceId]);
    $registrationsCount = $registrationsCount->fetch(PDO::FETCH_ASSOC)['count'];
    
    $pendingProofsCount = $pdo->prepare("SELECT COUNT(*) as count FROM proofs p 
                                        JOIN registrations r ON p.registration_id = r.id 
                                        WHERE p.status = 'pending' AND r.race_id = ?");
    $pendingProofsCount->execute([$raceId]);
    $pendingProofsCount = $pendingProofsCount->fetch(PDO::FETCH_ASSOC)['count'];
    
    $adminsCount = 0; // Race admin doesn't need to see other admins
    
    // Get event photos count for their race
    $eventPhotosCount = $pdo->prepare("SELECT COUNT(*) as count FROM event_photos WHERE race_id = ?");
    $eventPhotosCount->execute([$raceId]);
    $eventPhotosCount = $eventPhotosCount->fetch(PDO::FETCH_ASSOC)['count'];
}

include '../includes/header.php';
?>

<div class="container my-4">
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <?php if (isRaceAdmin()): ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>
        You are logged in as an admin for <strong><?php echo $_SESSION['admin_race_name']; ?></strong>.
    </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card primary">
                <div class="card-body">
                    <div class="icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="content">
                        <h3><?php echo $racesCount; ?></h3>
                        <p>Races</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card success">
                <div class="card-body">
                    <div class="icon">
                        <i class="bi bi-tags"></i>
                    </div>
                    <div class="content">
                        <h3><?php echo $categoriesCount; ?></h3>
                        <p>Categories</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card warning">
                <div class="card-body">
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="content">
                        <h3><?php echo $registrationsCount; ?></h3>
                        <p>Registrations</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card danger">
                <div class="card-body">
                    <div class="icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <div class="content">
                        <h3><?php echo $pendingProofsCount; ?></h3>
                        <p>Pending Proofs</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isSuperAdmin()): ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card info">
                <div class="card-body">
                    <div class="icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="content">
                        <h3><?php echo $adminsCount; ?></h3>
                        <p>Admins</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card secondary">
                <div class="card-body">
                    <div class="icon">
                        <i class="bi bi-images"></i>
                    </div>
                    <div class="content">
                        <h3><?php echo $eventPhotosCount; ?></h3>
                        <p>Event Photos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card secondary">
                <div class="card-body">
                    <div class="icon">
                        <i class="bi bi-images"></i>
                    </div>
                    <div class="content">
                        <h3><?php echo $eventPhotosCount; ?></h3>
                        <p>Event Photos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (isSuperAdmin()): ?>
                        <a href="races/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-event me-2"></i>Manage Races
                        </a>
                        <a href="races/add.php" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add New Race
                        </a>
                        <a href="admins/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus me-2"></i>Manage Admins
                        </a>
                        <a href="admins/add.php" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus-fill me-2"></i>Add New Admin
                        </a>
                       
                        <?php endif; ?>
                        
                        <a href="categories/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-tags me-2"></i>Manage Categories
                        </a>
                        <a href="forms/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-ui-checks me-2"></i>Manage Forms
                        </a>
                        <a href="proofs/index.php" class="btn btn-outline-warning">
                            <i class="bi bi-file-earmark-check me-2"></i>Review Payment Proofs
                        </a>
                        
                        <?php if (isSuperAdmin()): ?>
                        <a href="races/index.php" class="btn btn-outline-info">
                            <i class="bi bi-images me-2"></i>Manage Event Photos
                        </a>
                        <?php else: ?>
                        <a href="event_photos/index.php?race_id=<?php echo $_SESSION['admin_race_id']; ?>" class="btn btn-outline-info">
                            <i class="bi bi-images me-2"></i>Manage Event Photos
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Registrations</h5>
                </div>
                <div class="card-body">
                    <?php
                    if (isSuperAdmin()) {
                        $stmt = $pdo->query("SELECT r.id, r.participant_name, r.registration_date, r.status, ra.name as race_name, c.name as category_name, r.registration_code
                                            FROM registrations r 
                                            JOIN races ra ON r.race_id = ra.id 
                                            JOIN categories c ON r.category_id = c.id 
                                            ORDER BY r.registration_date DESC LIMIT 5");
                    } else {
                        $raceId = $_SESSION['admin_race_id'];
                        $stmt = $pdo->prepare("SELECT r.id, r.participant_name, r.registration_date, r.status, ra.name as race_name, c.name as category_name, r.registration_code
                                            FROM registrations r 
                                            JOIN races ra ON r.race_id = ra.id 
                                            JOIN categories c ON r.category_id = c.id 
                                            WHERE r.race_id = ? 
                                            ORDER BY r.registration_date DESC LIMIT 5");
                        $stmt->execute([$raceId]);
                    }
                    $recentRegistrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($recentRegistrations) > 0):
                    ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Race</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                   <th>Photos</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRegistrations as $registration): ?>
                                <tr>
                                    <td><?php echo $registration['participant_name']; ?></td>
                                    <td><?php echo $registration['race_name']; ?></td>
                                    <td><?php echo $registration['category_name']; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $registration['status']; ?>">
                                            <?php echo ucfirst($registration['status']); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
    <a href="/admin/event_photos/index.php?race_id=<?php echo $race['id']; ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-images"></i> Manage Photos
    </a>
</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/admin/registrations/view.php?id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($registration['status'] === 'pending'): ?>
                                            <a href="/admin/registrations/approve.php?id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-check"></i> Approve
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="/admin/registrations/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No recent registrations found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>